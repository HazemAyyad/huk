"""Inspect/repair a phpMyAdmin dump as text; never connect to a database.

Repairs only nullable foreign-key values whose parents are absent in the dump.
Leaves the original file, all rows, and all foreign-key definitions intact.
"""
from __future__ import annotations

import argparse
from collections import Counter, defaultdict
from dataclasses import dataclass
import hashlib
import json
from pathlib import Path
import re


@dataclass(frozen=True)
class Cell:
    start: int
    end: int
    value: object


@dataclass(frozen=True)
class ForeignKey:
    table: str
    name: str
    columns: tuple[str, ...]
    parent: str
    parent_columns: tuple[str, ...]
    clause: str


def decode(raw: str) -> object:
    raw = raw.strip()
    if raw.upper() == "NULL":
        return None
    if re.fullmatch(r"-?\d+", raw):
        return int(raw)
    if raw.startswith("'") and raw.endswith("'"):
        # Values other than identifiers are never printed or executed.
        return raw[1:-1].replace("''", "'")
    return raw


def identifier_list(raw: str) -> tuple[str, ...]:
    return tuple(re.findall(r"`([^`]+)`", raw))


def parse(text: str):
    schemas = {}
    for match in re.finditer(r"CREATE TABLE `([^`]+)`\s*\((.*?)\)\s*ENGINE=", text, re.S):
        schemas[match[1]] = {
            col[1]: "NOT NULL" not in col[2].upper()
            for col in re.finditer(r"^\s*`([^`]+)`\s+(.+)$", match[2], re.M)
        }
    rows = defaultdict(list)
    for insert in re.finditer(r"INSERT INTO `([^`]+)`\s*\((.*?)\)\s*VALUES\s*", text, re.S):
        table = insert[1]
        columns = identifier_list(insert[2])
        index = insert.end()
        while True:
            while index < len(text) and text[index] in " \r\n\t,":
                index += 1
            if index >= len(text) or text[index] == ";":
                break
            if text[index] != "(":
                raise ValueError(f"Unsupported INSERT syntax for {table}")
            index += 1
            start, depth, quoted = index, 1, False
            cells = []
            while index < len(text):
                char = text[index]
                if quoted:
                    if char == "\\":
                        index += 2
                        continue
                    if char == "'":
                        if index + 1 < len(text) and text[index + 1] == "'":
                            index += 2
                            continue
                        quoted = False
                elif char == "'":
                    quoted = True
                elif char == "(":
                    depth += 1
                elif char == ")":
                    depth -= 1
                    if depth == 0:
                        cells.append(Cell(start, index, decode(text[start:index])))
                        index += 1
                        break
                elif char == "," and depth == 1:
                    cells.append(Cell(start, index, decode(text[start:index])))
                    start = index + 1
                index += 1
            else:
                raise ValueError(f"Unterminated INSERT for {table}")
            if len(cells) != len(columns):
                raise ValueError(f"INSERT column/value mismatch for {table}")
            rows[table].append(dict(zip(columns, cells)))
    keys = []
    for alter in re.finditer(r"ALTER TABLE `([^`]+)`\s+(.*?);", text, re.S):
        for key in re.finditer(
            r"(ADD CONSTRAINT `([^`]+)` FOREIGN KEY\s*\((.*?)\)\s*"
            r"REFERENCES `([^`]+)`\s*\((.*?)\)([^,;]*))", alter[2], re.S,
        ):
            keys.append(ForeignKey(alter[1], key[2], identifier_list(key[3]),
                                   key[4], identifier_list(key[5]), key[1].strip()))
    expected = len(re.findall(r"ADD CONSTRAINT .*?FOREIGN KEY", text))
    if len(keys) != expected:
        raise ValueError("Not all foreign-key definitions were understood")
    return schemas, rows, keys


def inspect(text: str):
    schemas, rows, keys = parse(text)
    violations = []
    for key in keys:
        if key.table not in schemas or key.parent not in schemas:
            raise ValueError(f"Missing schema for {key.name}")
        parents = {tuple(row[col].value for col in key.parent_columns) for row in rows[key.parent]}
        for row in rows[key.table]:
            value = tuple(row[col].value for col in key.columns)
            if any(item is None for item in value):
                continue
            if value not in parents:
                violations.append((key, row, value))
    return schemas, rows, keys, violations


def repaired_text(text: str) -> tuple[str, list]:
    schemas, _, _, violations = inspect(text)
    changes = {}
    for key, row, _ in violations:
        if not all(schemas[key.table][col] for col in key.columns):
            raise ValueError(f"Refusing to repair required relationship {key.name}; parent data is needed")
        for col in key.columns:
            cell = row[col]
            changes[(cell.start, cell.end)] = "NULL"
    fixed = text
    for (start, end), value in sorted(changes.items(), reverse=True):
        fixed = fixed[:start] + value + fixed[end:]
    _, before_rows, before_keys, _ = inspect(text)
    _, after_rows, after_keys, remaining = inspect(fixed)
    if remaining or before_keys != after_keys:
        raise ValueError("Foreign-key verification failed after repair")
    if {key: len(value) for key, value in before_rows.items()} != {key: len(value) for key, value in after_rows.items()}:
        raise ValueError("Repair changed the row counts")
    return fixed, violations


def recovery_sql(text: str, violations: list) -> str:
    schemas, _, keys, _ = inspect(text)
    lines = [
        "-- Recover a partially imported copy of this dump; select the target DB first.",
        "-- No DROP, DELETE, TRUNCATE, or disabling of foreign-key checks.",
        "-- Nullable references with no parent become NULL. All rows are retained.",
        "SET NAMES utf8mb4;", "",
    ]
    repaired_keys = {key.name: key for key, _, _ in violations}
    for key in repaired_keys.values():
        if not all(schemas[key.table][col] for col in key.columns):
            raise ValueError(f"Required relationship: {key.name}")
        join = " AND ".join(f"c.`{col}` = p.`{parent}`" for col, parent in zip(key.columns, key.parent_columns))
        updates = ", ".join(f"c.`{col}` = NULL" for col in key.columns)
        nonnull = " AND ".join(f"c.`{col}` IS NOT NULL" for col in key.columns)
        lines.extend([f"-- {key.name}", f"UPDATE `{key.table}` c LEFT JOIN `{key.parent}` p ON {join}",
                      f"SET {updates} WHERE {nonnull} AND p.`{key.parent_columns[0]}` IS NULL;", ""])
    for key in keys:
        ddl = (f"ALTER TABLE `{key.table}` {key.clause}").replace("'", "''")
        lines.extend([
            f"-- Restore {key.name} only if missing (safe to resume).",
            "SET @haq_repair_sql = IF(EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS",
            f"  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{key.table}'",
            f"  AND CONSTRAINT_NAME = '{key.name}' AND CONSTRAINT_TYPE = 'FOREIGN KEY'),",
            f"  'DO 0', '{ddl}');",
            "PREPARE haq_repair_statement FROM @haq_repair_sql;",
            "EXECUTE haq_repair_statement;",
            "DEALLOCATE PREPARE haq_repair_statement;", "",
        ])
    lines.extend([
        "SELECT COUNT(*) AS foreign_key_count FROM information_schema.TABLE_CONSTRAINTS",
        "WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'FOREIGN KEY';",
        f"-- Expected foreign_key_count for this dump: {len(keys)}.",
        "SELECT @@FOREIGN_KEY_CHECKS AS foreign_key_checks;",
    ])
    return "\n".join(lines) + "\n"


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("source", type=Path)
    parser.add_argument("--fixed", type=Path)
    parser.add_argument("--recovery", type=Path)
    args = parser.parse_args()
    original = args.source.read_bytes()
    text = original.decode("utf-8-sig")
    schemas, rows, keys, violations = inspect(text)
    summary = {
        "tables": len(schemas), "foreign_keys": len(keys),
        "rows_by_table": {table: len(value) for table, value in rows.items() if value},
        "violations_by_constraint": dict(Counter(key.name for key, _, _ in violations)),
        "invalid_references": [
            {"table": key.table, "columns": key.columns, "row_id": row.get("id", Cell(0, 0, None)).value,
             "missing_parent": key.parent, "value": value}
            for key, row, value in violations
        ],
        "source_sha256": hashlib.sha256(original).hexdigest(),
    }
    for destination in [args.fixed, args.recovery]:
        if destination and (destination.resolve() == args.source.resolve() or destination.exists()):
            raise ValueError("Choose a new output path; original/existing files are never overwritten")
    if args.fixed:
        fixed, _ = repaired_text(text)
        args.fixed.write_text(fixed, encoding="utf-8", newline="")
        summary["fixed_file"] = str(args.fixed)
        summary["remaining_violations"] = len(inspect(fixed)[3])
    if args.recovery:
        args.recovery.write_text(recovery_sql(text, violations), encoding="utf-8", newline="")
        summary["recovery_file"] = str(args.recovery)
    if args.source.read_bytes() != original:
        raise ValueError("Source changed during inspection")
    print(json.dumps(summary, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()

"""Offline tests. No SQL engine or database is used."""
import importlib.util
from pathlib import Path
import sys
import unittest

script = Path(__file__).resolve().parents[2] / "tools" / "repair_sql_dump.py"
spec = importlib.util.spec_from_file_location("repair_sql_dump", script)
repair = importlib.util.module_from_spec(spec)
sys.modules[spec.name] = repair
spec.loader.exec_module(repair)


def fixture(nullable=True):
    field = "DEFAULT NULL" if nullable else "NOT NULL"
    return f"""
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB;
CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED {field},
  `name` text NOT NULL
) ENGINE=InnoDB;
INSERT INTO `users` (`id`) VALUES (1);
INSERT INTO `categories` (`id`, `created_by`, `name`) VALUES
(1, 1, 'valid'), (2, 11, 'comma, semicolon; and parentheses (x)'),
(3, NULL, 'apostrophe: don''t'), (4, 5, 'escape: don\\'t');
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
"""


class DumpRepairTest(unittest.TestCase):
    def test_nullable_orphans_become_null_while_all_other_cells_stay_identical(self):
        source = fixture()
        fixed, violations = repair.repaired_text(source)
        self.assertEqual(len(violations), 2)
        before = repair.inspect(source)
        after = repair.inspect(fixed)
        self.assertEqual(after[3], [])
        self.assertEqual(before[2], after[2])
        for table, rows in before[1].items():
            self.assertEqual(len(rows), len(after[1][table]))
            for old, new in zip(rows, after[1][table]):
                for column, cell in old.items():
                    if table == 'categories' and column == 'created_by' and old['id'].value in (2, 4):
                        self.assertIsNone(new[column].value)
                    else:
                        self.assertEqual(source[cell.start:cell.end], fixed[new[column].start:new[column].end])

    def test_required_relationships_are_never_dropped_or_silently_changed(self):
        with self.assertRaisesRegex(ValueError, 'required relationship'):
            repair.repaired_text(fixture(nullable=False))

    def test_recovery_preserves_delete_action_and_checks_constraints_before_creating(self):
        source = fixture()
        sql = repair.recovery_sql(source, repair.inspect(source)[3])
        self.assertIn('UPDATE `categories` c LEFT JOIN `users` p', sql)
        self.assertIn('c.`created_by` = NULL', sql)
        self.assertIn("CONSTRAINT_TYPE = 'FOREIGN KEY'", sql)
        self.assertIn('ON DELETE SET NULL', sql)
        self.assertNotIn('SET FOREIGN_KEY_CHECKS=0', sql)
        self.assertNotIn('DROP TABLE', sql)
        self.assertNotIn('DELETE FROM', sql)


if __name__ == '__main__':
    unittest.main()

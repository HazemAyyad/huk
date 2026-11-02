"""Extract publisher HTML blocks without executing markup or fetching assets."""
from html.parser import HTMLParser


class PublisherHTML(HTMLParser):
    def __init__(self, text):
        super().__init__(convert_charrefs=True)
        self.blocks = []
        self.active = None
        self.depth = 0
        self.body = []
        self.feed(text)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag in ("p", "h1", "h2", "h3", "h4") and self.active is None:
            self.active = {"tag": tag, "id": attrs.get("id"), "class": attrs.get("class", ""), "text": "", "links": []}
            self.depth = 1
        elif self.active and tag not in ("br", "hr", "img", "input", "meta", "link"):
            self.depth += 1
        if self.active and tag == "a" and attrs.get("href"):
            self.active["links"].append(attrs["href"])
        if self.active and tag == "br":
            self.active["text"] += "\n"

    def handle_endtag(self, tag):
        if self.active:
            self.depth -= 1
            if self.depth == 0:
                self.blocks.append(self.active)
                self.active = None

    def handle_data(self, text):
        if self.active:
            self.active["text"] += text

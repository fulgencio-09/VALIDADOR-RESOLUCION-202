from __future__ import annotations

import csv
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OFFICIAL = ROOT / "database" / "catalog" / "validation_rules_rped.csv"
VALIDATION_DIR = ROOT / "backend" / "app" / "Domain" / "Res202" / "Validation"

CODE_RE = re.compile(r"(?:['\"]code['\"]\s*=>\s*['\"]|self::(?:forbidden|afterCutoff|beforeBirth|relation|inValues|dateRelation|equalsValue|wildcards)\(\s*['\"])([A-Za-z]+\d{3})['\"]")


def main() -> int:
    with OFFICIAL.open(encoding="utf-8", newline="") as fh:
        official = [row["code"].strip() for row in csv.DictReader(fh) if row.get("code")]

    executable: dict[str, list[str]] = {}
    for path in sorted(VALIDATION_DIR.glob("*RuleCatalog.php")):
        text = path.read_text(encoding="utf-8")
        for code in CODE_RE.findall(text):
            executable.setdefault(code, []).append(str(path.relative_to(ROOT)))

    official_set = set(official)
    executable_set = set(executable)
    unknown = sorted(executable_set - official_set)
    duplicates = sorted(code for code, files in executable.items() if len(files) > 1)
    pending = [code for code in official if code not in executable_set]

    print(f"Official: {len(official_set)}")
    print(f"Executable: {len(executable_set)}")
    print(f"Pending: {len(pending)}")
    print(f"Unknown: {len(unknown)}")
    print(f"Duplicates: {len(duplicates)}")
    if unknown:
        print("UNKNOWN:", ", ".join(unknown))
    if duplicates:
        print("DUPLICATES:")
        for code in duplicates:
            print(f"  {code}: {' | '.join(executable[code])}")
    if pending:
        print("PENDING:", ", ".join(pending))
    return 1 if unknown or duplicates else 0


if __name__ == "__main__":
    raise SystemExit(main())

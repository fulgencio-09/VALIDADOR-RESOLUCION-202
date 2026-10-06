from __future__ import annotations

import sys
from collections import Counter, defaultdict
from datetime import date
from pathlib import Path

CUTOFF = "2026-09-30"
WILDCARDS = {"1800-01-01", "1805-01-01", "1810-01-01", "1825-01-01", "1830-01-01", "1835-01-01", "1845-01-01"}


def age_months(value: str) -> int | None:
    if not value or value in WILDCARDS:
        return None
    birth = date.fromisoformat(value)
    cutoff = date.fromisoformat(CUTOFF)
    if birth > cutoff or birth < date(1900, 1, 1):
        return None
    return (cutoff.year - birth.year) * 12 + cutoff.month - birth.month - (cutoff.day < birth.day)


def age(r):
    value = age_months(r[9])
    if value is None:
        raise ValueError(f"Edad no calculable para consecutivo {r[1]}")
    return value


def build_rules():
    return {
        "Error294": lambda r: r[49] != "1845-01-01" and (age(r) < 120 or age(r) >= 720),
        "Error296": lambda r: r[49] != "1845-01-01" and r[50] == "1845-01-01",
        "Error299": lambda r: r[50] != "1845-01-01" and (age(r) < 120 or age(r) >= 720),
        "Error300": lambda r: r[49] > "1900-01-01" and r[50] > "1900-01-01" and r[49] > r[50],
        "Error301": lambda r: r[50] not in {"1845-01-01", "1800-01-01"} and r[49] in {"1845-01-01", "1800-01-01"},
        "Error304": lambda r: r[53] != "1845-01-01" and age(r) < 120,
        "Error305": lambda r: r[53] == "1845-01-01" and age(r) >= 120,
        "Error306": lambda r: r[55] == "1845-01-01" and r[54] != "0",
        "Warning307": lambda r: r[54] != "0" and r[55] > "1900-01-01" and (age(r) < 120 or age(r) >= 720),
        "Error308": lambda r: r[54] == "0" and 120 <= age(r) < 720,
        "Error309": lambda r: r[55] != "1845-01-01" and r[54] == "0",
        "Error318": lambda r: r[56] > "1900-01-01" and r[58] > "1900-01-01" and r[56] > r[58],
        "Error328": lambda r: r[70] == "0" and 6 <= age(r) <= 27,
        "Error329": lambda r: r[71] == "0" and 24 <= age(r) <= 63,
        "Error341": lambda r: r[78] > "1900-01-01" and r[79] in {"0", "21"},
        "Error344": lambda r: r[80] > "1900-01-01" and r[81] in {"0", "21"},
        "Error346": lambda r: r[82] > "1900-01-01" and r[83] in {"0", "21"},
        "Error350": lambda r: r[84] != "1845-01-01" and r[85] == "0",
        "Error352": lambda r: r[87] > "1900-01-01" and (r[88] < "1" or r[88] > "20"),
        "Error354": lambda r: r[89] in {"1", "2", "3"} and (r[88] < "1" or r[88] > "18"),
        "Error355": lambda r: r[89] in {"1", "2", "3", "4"} and r[90] in {"0", "999"},
        "Error359": lambda r: r[94] in {"1", "3", "4", "5", "6"} and r[93] in {"1845-01-01", "1800-01-01"},
        "Error361": lambda r: r[96] > "1900-01-01" and r[97] not in {"1", "2", "3", "4", "5', '6', '7'},
        "Error362": lambda r: r[10] == "F" and r[96] == "1845-01-01" and age(r) >= 600,
        "Error364": lambda r: age(r) < 420 and r[97] != "0",
        "Error367": lambda r: (r[100] == "1800-01-01" or r[100] > "1900-01-01") and r[101] == "0",
        "Error368": lambda r: r[99] > "1900-01-01" and r[100] > "1900-01-01" and r[100] <= r[99],
        "Error369": lambda r: r[101] in {"1", "2", "3", "4", "5"} and r[100] <= "1900-01-01",
        "Error371": lambda r: r[106] > "1900-01-01" and (r[107] <= "0" or r[107] >= "998"),
        "Error375": lambda r: r[112] > "1900-01-01" and r[113] not in {"1", "2", "3"},
        "Error379": lambda r: r[14] == "1" and any((r[23] == "0", r[35] == "0", r[59] == "0", r[60] == "0", r[61] == "0", r[33] == "1845-01-01", r[56] == "1845-01-01", r[58] == "1845-01-01")),
    }


def main():
    if len(sys.argv) != 2:
        raise SystemExit("Uso: python tools/test_rped_batch294.py <archivo.txt>")
    txt = Path(sys.argv[1])
    lines = txt.read_text(encoding="utf-8").splitlines()
    records = [line.split("|") for line in lines if line.startswith("2|")]
    if len(lines) != 432 or len(records) != 431:
        raise SystemExit(f"Estructura inesperada: lineas={len(lines)}, registros={len(records)}")
    if lines[0].split("|") != ["1", "EPSS41", "2026-09-01", "2026-09-30", "431"]:
        raise SystemExit("Registro de control distinto al TXT de prueba documentado")

    rules = build_rules()
    allowed = {
        380: (33, {"1800-01-01", "1845-01-01"}), 381: (29, {"1800-01-01"}), 382: (31, {"1800-01-01"}),
        383: (49, {"1800-01-01", "1845-01-01"}), 384: (50, {"1800-01-01", "1845-01-01"}),
        385: (51, WILDCARDS), 386: (52, WILDCARDS), 387: (53, WILDCARDS), 388: (55, WILDCARDS),
        389: (56, WILDCARDS), 390: (58, {"1800-01-01", "1845-01-01"}), 391: (62, WILDCARDS),
        392: (63, WILDCARDS), 393: (64, WILDCARDS), 394: (65, WILDCARDS), 395: (66, WILDCARDS),
        396: (67, WILDCARDS), 398: (69, WILDCARDS), 399: (72, WILDCARDS),
    }
    for number, (field, accepted) in allowed.items():
        rules[f"Error{number}"] = lambda r, field=field, accepted=accepted: r[field] in WILDCARDS and r[field] not in accepted

    counts = Counter()
    examples = defaultdict(list)
    for line_no, record in enumerate(records, start=2):
        if len(record) != 119:
            raise SystemExit(f"Registro inválido en línea {line_no}: {len(record)} campos")
        for code, rule in rules.items():
            if rule(record):
                counts[code] += 1
                if len(examples[code]) < 5:
                    examples[code].append((line_no, record[1]))

    print(f"records={len(records)} rules={len(rules)}")
    for code in rules:
        print(f"{code}: {counts[code]}")
    for code, rows in examples.items():
        print(f"{code}: {rows}")


if __name__ == "__main__":
    main()

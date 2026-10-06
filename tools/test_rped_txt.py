"""Prueba estructural y reglas RPED Error020-Error096, Error120-159 y Error171-182 sobre un TXT real."""
from __future__ import annotations
import re
import sys
from datetime import date
from pathlib import Path

EXPECTED_RECORDS = 431
EXPECTED_FIELDS = 119
CUTOFF_DATE = date(2026, 9, 30)
WILDCARD_DATES = {"1800-01-01", "1805-01-01", "1810-01-01", "1825-01-01", "1830-01-01", "1835-01-01", "1845-01-01"}


def age_months(value: str) -> int:
    birth = date.fromisoformat(value)
    months = (CUTOFF_DATE.year - birth.year) * 12 + CUTOFF_DATE.month - birth.month
    if CUTOFF_DATE.day < birth.day:
        months -= 1
    return months


def _error676(record: list[str]) -> bool:
    identifier_type, identifier = record[3], record[4]
    ranges = {"CC": (None, 10), "TI": (None, 11), "CE": (3, 7), "CD": (None, 11), "PA": (3, 16), "SC": (None, 9), "PE": (3, 15)}
    if identifier_type not in ranges:
        return False
    minimum, maximum = ranges[identifier_type]
    length = len(identifier)
    return (minimum is not None and length < minimum) or (maximum is not None and length > maximum)


def _before_birth(record: list[str], variable: int) -> bool:
    value, birth = record[variable], record[9]
    if value in WILDCARD_DATES or birth in WILDCARD_DATES or not value or not birth:
        return False
    try:
        return date.fromisoformat(value) <= date.fromisoformat(birth)
    except ValueError:
        return False


def check(path: Path) -> dict[str, int]:
    lines = path.read_text(encoding="utf-8").splitlines()
    assert len(lines) == EXPECTED_RECORDS + 1, len(lines)
    control = lines[0].split("|")
    assert control == ["1", "EPSS41", "2026-09-01", "2026-09-30", "431"], control
    records = [line.split("|") for line in lines[1:]]
    assert all(row[0] == "2" for row in records)
    assert all(len(row) == EXPECTED_FIELDS for row in records)
    assert all(row[1] == str(i) for i, row in enumerate(records, 1))

    violations = {
        "Error020": sum(r[9] == "" for r in records),
        "Error030": sum(r[14] in {"1", "2", "21"} and r[10] != "F" for r in records),
        "Error037": sum(r[22] in {"4", "5", "21"} and r[10] != "M" for r in records),
        "Error038": sum(age_months(r[9]) < 480 and r[10] == "M" and (r[22] != "0" or r[64] != "1845-01-01") for r in records),
        "Error041": sum(r[30] == "999" and r[29] != "1800-01-01" for r in records),
        "Error043": sum(r[32] == "999" and r[31] != "1800-01-01" for r in records),
        "Error047": sum(r[47] != "0" and r[10] != "F" for r in records),
        "Error049": sum(r[49] != "1845-01-01" and r[10] != "F" for r in records),
        "Error050": sum(r[50] != "1845-01-01" and r[10] != "F" for r in records),
        "Error051": sum(r[51] != "1845-01-01" and r[10] == "M" and age_months(r[9]) >= 7 for r in records),
        "Error063": sum(r[70] in {"1", "16", "17", "18", "20", "21"} and (age_months(r[9]) < 6 or age_months(r[9]) > 27) for r in records),
        "Error064": sum(r[71] in {"1", "16", "17", "18", "20", "21"} and (age_months(r[9]) < 24 or age_months(r[9]) > 63) for r in records),
        "Error069": sum(r[86] != "0" and (r[10] != "F" or age_months(r[9]) < 120) for r in records),
        "Error070": sum(r[87] != "1845-01-01" and age_months(r[9]) < 120 for r in records),
        "Error071": sum(r[87] != "1845-01-01" and r[10] != "F" for r in records),
        "Error072": sum(r[88] != "0" and age_months(r[9]) < 120 for r in records),
        "Error073": sum(r[88] != "0" and r[10] != "F" for r in records),
        "Error074": sum(r[89] in {"1", "2", "3", "4", "999"} and age_months(r[9]) < 120 for r in records),
        "Error075": sum(r[89] != "0" and r[10] != "F" for r in records),
        "Error076": sum(r[90] != "0" and age_months(r[9]) <= 120 for r in records),
        "Error077": sum(r[90] != "0" and r[10] != "F" for r in records),
        "Error078": sum(r[91] != "1845-01-01" and age_months(r[9]) < 120 for r in records),
        "Error082": sum(r[93] != "1845-01-01" and age_months(r[9]) <= 120 for r in records),
        "Error083": sum(r[93] != "1845-01-01" and r[10] != "F" for r in records),
        "Error084": sum(r[94] in {"1", "3", "4", "5", "6", "21"} and age_months(r[9]) <= 120 for r in records),
        "Error085": sum(r[94] != "0" and r[10] != "F" for r in records),
        "Error088": sum(r[96] != "1845-01-01" and age_months(r[9]) < 420 for r in records),
        "Error089": sum(r[96] != "1845-01-01" and r[10] != "F" for r in records),
        "Error090": sum(r[97] != "0" and age_months(r[9]) < 420 for r in records),
        "Error091": sum(r[97] != "0" and r[10] != "F" for r in records),
        "Error094": sum(r[99] > "1900-01-01" and r[10] != "F" for r in records),
        "Error095": sum(r[100] > "1900-01-01" and r[10] != "F" for r in records),
        "Error096": sum(r[101] in {"1", "2", "3", "4", "5", "21"} and r[10] != "F" for r in records),
        "Error120": sum(r[9] > "2026-09-30" for r in records if r[9] not in WILDCARD_DATES),
        "Error121": sum(r[29] > "2026-09-30" for r in records if r[29] not in WILDCARD_DATES),
        "Error122": sum(r[31] > "2026-09-30" for r in records if r[31] not in WILDCARD_DATES),
        "Error123": sum(r[49] > "2026-09-30" for r in records if r[49] not in WILDCARD_DATES),
        "Error124": sum(r[50] > "2026-09-30" for r in records if r[50] not in WILDCARD_DATES),
        "Error125": sum(r[51] > "2026-09-30" for r in records if r[51] not in WILDCARD_DATES),
        "Error126": sum(r[52] > "2026-09-30" for r in records if r[52] not in WILDCARD_DATES),
        "Error127": sum(r[53] > "2026-09-30" for r in records if r[53] not in WILDCARD_DATES),
        "Error128": sum(r[55] > "2026-09-30" for r in records if r[55] not in WILDCARD_DATES),
        "Error129": sum(r[56] > "2026-09-30" for r in records if r[56] not in WILDCARD_DATES),
        "Error130": sum(r[58] > "2026-09-30" for r in records if r[58] not in WILDCARD_DATES),
        "Error131": sum(r[62] > "2026-09-30" for r in records if r[62] not in WILDCARD_DATES),
        "Error139": sum(r[72] > "2026-09-30" for r in records if r[72] not in WILDCARD_DATES),
        "Error144": sum(r[80] > "2026-09-30" for r in records if r[80] not in WILDCARD_DATES),
        "Error145": sum(r[82] > "2026-09-30" for r in records if r[82] not in WILDCARD_DATES),
        "Error146": sum(r[84] > "2026-09-30" for r in records if r[84] not in WILDCARD_DATES),
        "Error147": sum(r[87] > "2026-09-30" for r in records if r[87] not in WILDCARD_DATES),
        "Error148": sum(r[91] > "2026-09-30" for r in records if r[91] not in WILDCARD_DATES),
        "Error149": sum(r[93] > "2026-09-30" for r in records if r[93] not in WILDCARD_DATES),
        "Error150": sum(r[96] > "2026-09-30" for r in records if r[96] not in WILDCARD_DATES),
        "Error151": sum(r[99] > "2026-09-30" for r in records if r[99] not in WILDCARD_DATES),
        "Error152": sum(r[100] > "2026-09-30" for r in records if r[100] not in WILDCARD_DATES),
        "Error155": sum(r[106] > "2026-09-30" for r in records if r[106] not in WILDCARD_DATES),
        "Error157": sum(r[110] > "2026-09-30" for r in records if r[110] not in WILDCARD_DATES),
        "Error158": sum(r[111] > "2026-09-30" for r in records if r[111] not in WILDCARD_DATES),
        "Error159": sum(r[112] > "2026-09-30" for r in records if r[112] not in WILDCARD_DATES),
        "Error171": sum(_before_birth(r,29) for r in records),
        "Error172": sum(_before_birth(r,31) for r in records),
        "Error173": sum(_before_birth(r,49) for r in records),
        "Error174": sum(_before_birth(r,50) for r in records),
        "Error175": sum(_before_birth(r,51) for r in records),
        "Error176": sum(_before_birth(r,52) for r in records),
        "Error177": sum(_before_birth(r,53) for r in records),
        "Error178": sum(_before_birth(r,55) for r in records),
        "Error179": sum(_before_birth(r,56) for r in records),
        "Error180": sum(_before_birth(r,58) for r in records),
        "Error181": sum(_before_birth(r,62) for r in records),
        "Error182": sum(_before_birth(r,63) for r in records),
        "Error653": sum(r[113] not in {"1", "2", "3", "4", "21"} for r in records if r[113] != ""),
        "Error655": sum(r[114] not in {"0", "4", "5", "6", "21"} for r in records if r[114] != ""),
        "Error656": sum(r[115] != "0" for r in records),
        "Error657": sum(r[116] != "0" for r in records),
        "Error665": sum(r[117] not in {"0", "4", "5", "6", "21"} for r in records if r[117] != ""),
        "Error676": sum(_error676(r) for r in records),
        "Error677": sum(r[9] < "1900-01-01" for r in records if r[9] != ""),
        "Error678": sum(r[102] not in {"0", "21"} and re.fullmatch(r"\d{12}", r[102]) is None for r in records if r[102] != ""),
    }
    assert all(value == 0 for value in violations.values()), violations
    return {"records": len(records), **violations}


if __name__ == "__main__":
    if len(sys.argv) != 2:
        raise SystemExit("Uso: python tools/test_rped_txt.py <archivo.txt>")
    result = check(Path(sys.argv[1]))
    print("OK", result)

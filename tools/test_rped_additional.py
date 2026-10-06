"""Prueba RPED de las reglas adicionales Error220, 222, 223, 227, 232, 237, 242, 243 y 244 sobre el TXT real."""
from __future__ import annotations
import re
import sys
from datetime import date, timedelta
from pathlib import Path

CUTOFF=date(2026,9,30)

def age_months(value:str)->int:
    b=date.fromisoformat(value); m=(CUTOFF.year-b.year)*12+CUTOFF.month-b.month
    if CUTOFF.day<b.day:m-=1
    return m

def d(value:str):
    try:return date.fromisoformat(value)
    except ValueError:return None

def check(path:Path)->dict[str,int]:
    lines=path.read_text(encoding='utf-8').splitlines(); records=[x.split('|') for x in lines[1:]]
    assert len(records)==431 and all(len(r)==119 for r in records)
    violations={
        'Error220':sum(bool(r[4]) and re.fullmatch(r'[A-Z0-9]+',r[4]) is None for r in records),
        'Error222':sum(r[14]!='0' and (age_months(r[9])<120 or age_months(r[9])>=720) for r in records),
        'Error223':sum(r[10]=='F' and 120<=age_months(r[9])<720 and r[14]=='0' for r in records),
        'Error227':sum((r[16] in {'4','5','21'} and age_months(r[9])<720) or (r[16]=='0' and age_months(r[9])>=720) for r in records),
        'Error232':sum(r[18]=='1' and (r[113]=='4' or r[112]=='1845-01-01') for r in records),
        'Error237':sum(d(r[64]) and d(r[64])>date(1900,1,1) and not (r[22] in {'4','5'} and r[10]=='M' and age_months(r[9])>=480) for r in records),
        'Error242':sum(d(r[33]) is not None and d(r[33])>CUTOFF+timedelta(days=280) for r in records),
        'Error243':sum(d(r[33]) is not None and d(r[56]) is not None and d(r[33])>date(1900,1,1) and d(r[56])>date(1900,1,1) and d(r[33])<=d(r[56]) for r in records),
        'Error244':sum(r[14]!='1' and (r[23]!='0' or r[35]!='0' or r[59]!='0' or r[60]!='0' or r[61]!='0' or r[33]!='1845-01-01' or r[56]!='1845-01-01' or r[58]!='1845-01-01') for r in records),
    }
    print('records=',len(records)); print(violations); return violations

if __name__=='__main__':
    if len(sys.argv)!=2: raise SystemExit('Uso: python tools/test_rped_additional.py <archivo.txt>')
    check(Path(sys.argv[1]))

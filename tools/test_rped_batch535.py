from __future__ import annotations

import sys
from collections import Counter
from datetime import date
from pathlib import Path

CUTOFF=date(2026,9,30); NO_DATA={'1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01'}

def age(r):
    b=date.fromisoformat(r[9]); return (CUTOFF.year-b.year)*12+CUTOFF.month-b.month-(CUTOFF.day<b.day)

def main():
    if len(sys.argv)!=2: raise SystemExit('Uso: python tools/test_rped_batch535.py <archivo.txt>')
    lines=Path(sys.argv[1]).read_text(encoding='utf-8').splitlines(); records=[x.split('|') for x in lines[1:]]
    assert len(lines)==432 and len(records)==431 and all(len(r)==119 for r in records)
    rules={
      'Error535':lambda r:r[66]>'1900-01-01' and r[36] in {'0','21'},
      'Error536':lambda r:r[66] in NO_DATA and (age(r)<600 or age(r)>900 or r[36]!='21'),
      'Error537':lambda r:r[66]=='1845-01-01' and (r[36]!='0' or 600<=age(r)<=900),
      'Error538':lambda r:r[36] not in {'0','2','3','4','5','6','21'},
      'Error539':lambda r:r[37] in {'4','5'} and r[69]<='1900-01-01',
      'Error540':lambda r:r[37] not in {'0','4','5','21'},
    }
    counts=Counter(code for r in records for code,rule in rules.items() if rule(r))
    print(f'records={len(records)} rules={len(rules)}')
    for code in rules: print(f'{code}: {counts[code]}')

if __name__=='__main__': main()

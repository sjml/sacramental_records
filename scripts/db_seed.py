import os
import sys
import csv
import sqlite3

os.chdir(os.path.join(os.path.dirname(__file__), ".."))

CSV_PATH = os.path.join("tmp", "records.csv") # exported from old Google Sheet
DB_PATH = os.path.join("site", "sacraments.db")

if os.path.exists(DB_PATH):
    print("ERROR: Database file already exists!")
    sys.exit(1)

db = sqlite3.connect(DB_PATH)
db.execute("""CREATE TABLE sacraments(
            id INTEGER PRIMARY KEY,
            date TEXT,
            sacrament TEXT,
            name_number TEXT,
            location TEXT,
            notes TEXT
        );""")

with open(CSV_PATH, "r") as records_file:
    reader = csv.reader(records_file)
    next(reader) # skip header
    for row in reader:
        date_components = row[0].split("/")
        date = f"{date_components[2]}-{int(date_components[0]):02d}-{int(date_components[1]):02d}"
        sac = row[1]
        name_or_number = row[2]
        if len(name_or_number) == 0:
            name_or_number = None
        location = row[3]
        notes = row[4]
        if len(notes) == 0:
            notes = None

        db.execute("INSERT INTO sacraments (date, sacrament, name_number, location, notes) VALUES(?, ?, ?, ?, ?)", (date, sac, name_or_number, location, notes))


db.commit()

db.close()

BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "quartile" (
	"name"	TEXT NOT NULL,
	"year"	INTEGER NOT NULL,
	"issn"	TEXT NOT NULL,
	"eissn"	TEXT NOT NULL,
	"quartile"	TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS "yearissn" ON "quartile" (
	"year"	ASC,
	"issn"	ASC,
	"eissn"	ASC
);
CREATE INDEX IF NOT EXISTS "yearname" ON "quartile" (
	"year"	ASC,
	"name"	ASC
);
COMMIT;

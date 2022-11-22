# wos2q
convert web of science output to publication list, including Quartile values

To be complete and clear, you need a complete set of journals quartile values, from year 1997-to date (2021), including journal name, issn, eissn, and quartile values. An example set included in asset folder. You need to add this journals to a SQLite empty database.

The files in the asset is not needed to software to be run, they are just needed to produce a complete set of quartile list.

Program calculates Quartile values, according to, if possible "Publication Year". If journal is only in electronic format, then calculates quartile value according to "Early Access" year. You can change that behavior by ticking "Prefer ea.Year for computing Q" in the menu. InCites, calculates Quartile values according to Early Access Year.

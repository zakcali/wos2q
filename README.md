# wos2q
converts tab delimited, full web of science output to publication list, including Quartile values

To be complete and clear, you need a complete set of journals quartile values, from year 1997-to date (2021), including journal name, issn, eissn, and quartile values. An example set included in asset folder. You need to add this journals to a SQLite empty database.

The files in the asset is not needed to software to be run, they are just needed to produce a complete set of quartile list.

Program calculates Quartile values, according to, if possible "Publication Year". If journal is only in electronic format, then calculates quartile value according to "Early Access" year. You can change that behavior by ticking "Prefer ea.Year for computing Q" checkbox from the menu. InCites, calculates Quartile values according to Early Access Year.

There are two types of outputs:

1- Doesn't include author-address fields. This is the default output

2- If user ticks "Include addresses" checkbox from the menu, software Reads C1 and RP fields from Web of Science output and writes Addresses and Correspondence fields to the end of output.  Another utility, https://github.com/zakcali/q2authors reads that file, and tries to split authors of a specific institution.

There are .htaccess files for Apache server, because the asset is copyrighted by Clarivate analytics. You must prevent access from outside of the campus, and you must also restrict the database file to be downloaded.

About
=====

pgnui is an attempt to a generic frontend for PostgreSQL databases.

The concept is that once you have a database schema created, you should have
the best possible generic frontend with no coding needed. The other way should
also hold true: to get a generic application running, the only thing that
should be strictly needed is a good database schema created.


Approach
========

To accomplish the above, pgnui needs to have as much data as possible from the
database. Thus:

* Table relationships are taken from FOREIGN KEY constraints.

* Record identifiers are taken from PRIMARY KEY constraints.

* Users log in directly to the database.

Pgnui will try to get the best from PostgreSQL. There are some limitations on
PostgreSQL. Some can be worked around by pgnui by the use of conventions, but
others will need to get addressed on the PGSQL itself.



Conventions
===========

* Pretty names for database, table and column objects are written in the SQL
comment for the object. Possibly, comments will have a syntax in the future,
for example, "plural_pretty_name\[|singular_pretty_name\]", like
"Types of fish|Type of fish".



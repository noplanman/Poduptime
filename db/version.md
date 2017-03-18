If new install import tables.sql and do not perform migrations

If upgrading migrations are:
v1.0 -> v2.0 = migration00001.sql
v2.0 -> v2.1 = migration00002.sql
v2.1 -> v2.x = migration00003.sql

To support the original apiv1 you should import:
pods_apiv1.sql 
into your db as often as you want your api updated.

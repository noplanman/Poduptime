If new install import tables.sql

If upgrading migrations are:
v1.0 -> v2.0 = migration00001.sql

To support the original apiv1 you should import:
pods_apiv1.sql 
into your db as often as you want your api updated.

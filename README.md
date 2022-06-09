# PMB Para XarxaLlibres

Repositorio de codigo para las modificaciones en PHP del PMB de LliureX para dar soporte a XarxaLlibres.



# Algunos ejemplos de SQL 

ACTUALIZANDO expl_cb

```sql
UPDATE temp_xarxa_exemplaires 
SET expl_cb = CONCAT (expl_cb , 'x')
WHERE expl_cb IN (
    SELECT expl_cb 
    FROM exemplaires
    )
    
```
 
 MERGE FINAL

```sql
INSERT INTO exemplaires (
    SELECT * 
    FROM temp_xarxa_exemplaires
    WHERE expl_id not in (
        SELECT expl_id 
        FROM exemplaires
        )
    )
```

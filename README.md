# Algunos ejemplos de SQL 

-- ACTUALIZANDO expl_cb

UPDATE temp_xarxa_exemplaires 
SET expl_cb = CONCAT (expl_cb , 'x')
WHERE expl_cb IN (
    SELECT expl_cb 
    FROM exemplaires
    )
    
 
 
-- MERGE FINAL

INSERT INTO exemplaires (
    SELECT * 
    FROM temp_xarxa_exemplaires
    WHERE expl_id not in (
        SELECT expl_id 
        FROM exemplaires
        )
    )

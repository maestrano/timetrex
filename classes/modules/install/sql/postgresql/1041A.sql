update authorizations as a set object_type_id = CASE WHEN (select type_id from request as z where z.id = a.object_id) > 0 THEN (select type_id from request as z where z.id = a.object_id)+1000 ELSE 1100 END where a.object_type_id = 50;
ALTER TABLE company ADD COLUMN industry_id integer DEFAULT 0;

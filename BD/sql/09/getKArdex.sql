-- FUNCTION: public.getkardex(text, text, bigint, bigint, bigint)

-- DROP FUNCTION public.getkardex(text, text, bigint, bigint, bigint);

CREATE OR REPLACE FUNCTION public.getkardex(
	fecha_inicio text,
	fecha_fin text,
	_sucursal bigint,
	_almacen bigint,
	_producto bigint)
    RETURNS TABLE(date text, type_id smallint, branch_office_name text, storage_id bigint, storage_name text, category_code text, line_code text, product_code text, product_id bigint, folio bigint, qty double precision, stock double precision, idx bigint) 
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
    ROWS 1000

AS $BODY$
DECLARE

  fila_detalle RECORD;

  id_producto numeric := 0;

  id_almacen numeric := 0;

  saldo numeric := 0.0;
  
  numero_idx numeric := 0;

  today date:= current_date;
  start date:= '2000-01-01';

  startstock text:= null;

 BEGIN

 IF fecha_fin is null or fecha_fin = '' THEN

  fecha_fin := today::text;
 END IF;

 IF fecha_inicio is  null or fecha_inicio = '' THEN
  fecha_inicio := start;
 END IF;

 FOR date,type_id, branch_office_name, storage_id, storage_name, category_code, line_code, product_code, product_id, folio, qty, stock, idx IN 
 (select TO_CHAR(v_m.date, 'dd/mm/yyyy') as date,v_m.type_id,bo.name AS branch_office_name,v_m.storage_id,s.name AS storage_name,
    c.code AS category_code,l.code AS line_code,p.code AS product_code,v_m.product_id,v_m.folio,v_m.qty,coalesce(st.stock,0) as stock, null
    from v_movements v_m
      join wms_storages s on s.id = v_m.storage_id
      JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
    left join stock_date((fecha_inicio::date + '-1 days'::INTERVAL)::DATE::TEXT) st on st.storage_id = v_m.storage_id and st.product_id = v_m.product_id
    JOIN wms_products AS p ON p.id = v_m.product_id
      JOIN wms_lines AS l ON l.id = p.line_id
    JOIN wms_categories AS c ON c.id = l.category_id
    where v_m.date BETWEEN fecha_inicio::date and fecha_fin::date
    and (s.branch_office_id = _sucursal or _sucursal is null) and ( s.id = _almacen or _almacen is null ) and (p.id = _producto or _producto is null)
    order by v_m.storage_id,v_m.product_id,v_m.date,
    CASE v_m.type_id 
    WHEN 3 then 1 
    WHEN 1 then 2 
    WHEN 4 then 3 
    WHEN 2 then 4 
    WHEN 5 then 5
      when 6 then 6 --linea añadida por gaby
    else 0 END, v_m.folio) LOOP
  IF (id_producto != product_id OR id_almacen != storage_id) THEN
        saldo = stock;
  END IF;
  IF type_id = 1 OR type_id = 4 THEN
    saldo = saldo + qty;
  END IF;

  IF type_id = 3 THEN
    saldo = qty;
  END IF;
  IF type_id = 2 OR type_id = 5 OR type_id = 6 THEN --linea añadida por gaby
    saldo = saldo - qty;
  END IF;

  numero_idx = numero_idx + 1;
  idx = numero_idx;
  stock = saldo;

  id_producto = product_id;

  id_almacen = storage_id;

  RETURN NEXT;
 END LOOP;
 RETURN;
 --RETURN query EXECUTE cadena_select;
 END
$BODY$;

ALTER FUNCTION public.getkardex(text, text, bigint, bigint, bigint)
    OWNER TO postgres;

GRANT EXECUTE ON FUNCTION public.getkardex(text, text, bigint, bigint, bigint) TO PUBLIC;

GRANT EXECUTE ON FUNCTION public.getkardex(text, text, bigint, bigint, bigint) TO postgres;






-- View: public.v_product_stock_price

-- DROP VIEW public.v_product_stock_price;

CREATE OR REPLACE VIEW public.v_product_stock_price
 AS
 SELECT v_m.storage_id,
    v_m.product_id,
    sum(
        CASE
            WHEN v_m.type_id = 1 THEN v_m.unit_price
            ELSE 0::numeric
        END) AS price,
    sum(
        CASE
            WHEN v_m.type_id = 1 OR v_m.type_id = 3 OR v_m.type_id = 4 THEN v_m.qty
            WHEN v_m.type_id = 2 OR v_m.type_id = 5 OR v_m.type_id = 6 THEN - v_m.qty
            ELSE NULL::double precision
        END) AS stock
   FROM v_movements v_m
     LEFT JOIN v_initial_product_folio v_i ON v_m.storage_id = v_i.storage_id AND v_i.product_id = v_m.product_id
  --WHERE v_m.date >= v_i.date AND v_m.folio >= v_i.last_folio OR v_i.date IS NULL
  GROUP BY v_m.storage_id, v_m.product_id;

ALTER TABLE public.v_product_stock_price
    OWNER TO postgres;

GRANT ALL ON TABLE public.v_product_stock_price TO postgres;
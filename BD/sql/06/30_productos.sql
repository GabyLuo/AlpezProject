ALTER TABLE IF EXISTS public.wms_products_minimum_stock
    ADD COLUMN IF NOT EXISTS min_operation numeric(15, 2);

ALTER TABLE IF EXISTS public.wms_products_minimum_stock
    ADD COLUMN IF NOT EXISTS max_operation numeric(15, 2);
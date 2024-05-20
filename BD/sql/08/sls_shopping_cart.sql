ALTER TABLE public.sls_shopping_cart
    ADD COLUMN order_date date;

ALTER TABLE public.sls_shopping_cart
    ADD COLUMN pledge_date date;
	
ALTER TABLE public.sls_shopping_cart
    ADD COLUMN supplier_id bigint;
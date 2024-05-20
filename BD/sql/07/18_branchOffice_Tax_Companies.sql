CREATE SEQUENCE public.wms_branch_office_tax_companies_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 9223372036854775807
    CACHE 1;





CREATE TABLE IF NOT EXISTS public.wms_branch_office_tax_companies
(
    id bigint NOT NULL DEFAULT nextval('wms_branch_office_tax_companies_id_seq'::regclass),
    created timestamp without time zone,
    created_by bigint,
    updated timestamp without time zone,
    updated_by bigint,
    branch_office_id bigint NOT NULL,
    razon_social text COLLATE pg_catalog."default" NOT NULL,
    rfc text COLLATE pg_catalog."default" NOT NULL,
    lugar_expedicion bigint NOT NULL,
    metodo_pago character varying(3) COLLATE pg_catalog."default" NOT NULL,
    forma_pago bigint NOT NULL,
    uso_cfdi bigint NOT NULL,
    serie text COLLATE pg_catalog."default",
    email text COLLATE pg_catalog."default",
    CONSTRAINT wms_branch_offices_tax_companies PRIMARY KEY (id)
)
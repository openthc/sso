\c openthc_auth

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET search_path TO public;
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

CREATE FUNCTION public.log_delta_trigger() RETURNS trigger
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
BEGIN

    CASE TG_OP
    WHEN 'UPDATE' THEN
        INSERT INTO public.log_delta (op, tb, pk, v0, v1) VALUES (TG_OP, TG_TABLE_NAME, OLD.id, row_to_json(OLD), row_to_json(NEW));
        RETURN NEW;
    WHEN 'INSERT' THEN
        INSERT INTO public.log_delta (op, tb, pk, v1) VALUES (TG_OP, TG_TABLE_NAME, NEW.id, row_to_json(NEW));
        RETURN NEW;
    WHEN 'DELETE' THEN
        INSERT INTO public.log_delta (op, tb, pk, v0) VALUES (TG_OP, TG_TABLE_NAME, OLD.id, row_to_json(OLD));
        RETURN OLD;
    END CASE;

END;
$$;


CREATE FUNCTION public.updated_at_now_trigger() RETURNS trigger
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
BEGIN

    NEW.updated_at = now();
    RETURN NEW;

END;
$$;



--
-- Name: auth_company log_delta_company; Type: TRIGGER;
--

CREATE TRIGGER log_delta_company AFTER INSERT OR DELETE OR UPDATE ON auth_company FOR EACH ROW EXECUTE PROCEDURE public.log_delta_trigger();


--
-- Name: auth_contact log_delta_company; Type: TRIGGER;
--

CREATE TRIGGER log_delta_contact AFTER INSERT OR DELETE OR UPDATE ON auth_contact FOR EACH ROW EXECUTE PROCEDURE public.log_delta_trigger();

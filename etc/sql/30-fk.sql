
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

ALTER TABLE ONLY auth_company_company
    ADD CONSTRAINT auth_company_company_company_id_prime_fkey FOREIGN KEY (company_id_prime) REFERENCES auth_company(id);


--
-- Name: auth_company_company auth_company_company_company_id_child_fkey; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY auth_company_company
    ADD CONSTRAINT auth_company_company_company_id_child_fkey FOREIGN KEY (company_id_child) REFERENCES auth_company(id);


--
-- Name: auth_company_contact auth_company_contact_company_id_fkey; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY auth_company_contact
    ADD CONSTRAINT auth_company_contact_company_id_fkey FOREIGN KEY (company_id) REFERENCES auth_company(id);


--
-- Name: auth_company_contact auth_company_contact_contact_id_fkey; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY auth_company_contact
    ADD CONSTRAINT auth_company_contact_contact_id_fkey FOREIGN KEY (contact_id) REFERENCES auth_contact(id);

--
-- Service
--
ALTER TABLE ONLY auth_service_contact
    ADD CONSTRAINT auth_service_contact_service_id_fkey FOREIGN KEY (service_id) REFERENCES auth_service(id);

ALTER TABLE ONLY auth_service_contact
    ADD CONSTRAINT auth_service_contact_contact_id_fkey FOREIGN KEY (contact_id) REFERENCES auth_contact(id);

--
-- ACL Stuff
--

ALTER TABLE ONLY acl_service_object_action
    ADD CONSTRAINT acl_service_object_action_service_id_fkey FOREIGN KEY (service_id) REFERENCES auth_service(id);

ALTER TABLE ONLY acl_company_contact_service_object_action
    ADD CONSTRAINT acl_company_contact_service_object_action_company_id FOREIGN KEY (company_id) REFERENCES auth_company(id);

ALTER TABLE ONLY acl_company_contact_service_object_action
    ADD CONSTRAINT acl_company_contact_service_object_action_contact_id FOREIGN KEY (contact_id) REFERENCES auth_contact(id);

ALTER TABLE ONLY acl_company_contact_service_object_action
    ADD CONSTRAINT acl_company_contact_service_object_action_service_id FOREIGN KEY (service_id) REFERENCES auth_service(id);

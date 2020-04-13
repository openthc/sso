
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

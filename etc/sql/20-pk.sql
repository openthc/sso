
ALTER TABLE ONLY public.auth_company_contact
    ADD CONSTRAINT auth_company_contact_pkey PRIMARY KEY (company_id, contact_id);

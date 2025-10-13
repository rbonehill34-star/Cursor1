-- Update Clients Table for Cursor1
-- Add new fields to the clients table as requested

-- Add new fields to clients table
ALTER TABLE clients 
ADD COLUMN company_number VARCHAR(8) AFTER phone,
ADD COLUMN authentication_code VARCHAR(6) AFTER company_number,
ADD COLUMN utr_number VARCHAR(10) AFTER authentication_code,
ADD COLUMN partner_id INT AFTER utr_number,
ADD COLUMN year_end_work ENUM('Y', 'N') DEFAULT 'N' AFTER partner_id,
ADD COLUMN payroll ENUM('Y', 'N') DEFAULT 'N' AFTER year_end_work,
ADD COLUMN directors_sa ENUM('Y', 'N') DEFAULT 'N' AFTER payroll,
ADD COLUMN vat ENUM('Y', 'N') DEFAULT 'N' AFTER directors_sa,
ADD COLUMN vat_periods ENUM('MJSD', 'JAJO', 'FMAN') AFTER vat;

-- Add foreign key constraint for partner_id
ALTER TABLE clients 
ADD CONSTRAINT fk_clients_partner 
FOREIGN KEY (partner_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_clients_company_number ON clients(company_number);
CREATE INDEX IF NOT EXISTS idx_clients_utr_number ON clients(utr_number);
CREATE INDEX IF NOT EXISTS idx_clients_partner ON clients(partner_id);
CREATE INDEX IF NOT EXISTS idx_clients_vat ON clients(vat);

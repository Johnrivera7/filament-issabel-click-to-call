# Security

Report vulnerabilities privately to the repository owner.

## AMI exposure

- Never expose Asterisk AMI (port 5038) to the public internet.
- Use a dedicated AMI user with **originate-only** write permissions where possible.
- Restrict `permit` to the Laravel application server IP.
- Store `ISSABEL_PBX_AMI_SECRET` in `.env`, not in git.

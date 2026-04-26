# Deployment Checklist

## Pre-Deployment Review

- [ ] Review [SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md)
- [ ] Read [SETUP_AFTER_IMPROVEMENTS.md](SETUP_AFTER_IMPROVEMENTS.md)
- [ ] Check all modified files in [IMPROVEMENT_SUMMARY.md](IMPROVEMENT_SUMMARY.md)

## Local Testing (Development)

- [ ] Create logs directory: `mkdir -p app/logs`
- [ ] Copy environment template: `cp .env.example .env`
- [ ] Edit .env with your database credentials
- [ ] Set APP_ENV=development in .env
- [ ] Reimport database schema:
  - [ ] Open http://localhost/phpmyadmin
  - [ ] Select `dfsms` database
  - [ ] Import `database/schema.sql`
- [ ] Test login with `admin / admin123`
- [ ] Verify dashboard loads without errors
- [ ] Check `app/logs/php_errors.log` for any issues
- [ ] Test adding a product
- [ ] Test deleting a product
- [ ] Check logs for event entries

## Security Testing

- [ ] Test SQL injection (username: `admin' OR '1'='1`)
  - Should fail with "Invalid username or password"
- [ ] Test CSRF protection
  - Try submitting form from different domain
  - Should fail with "Security validation failed"
- [ ] Test password validation
  - Try empty password → should show error
  - Try short password → should work
- [ ] Verify session cookies have HttpOnly flag
  - Open DevTools > Application > Cookies
  - Check HttpOnly is checked for session cookie

## Staging Deployment

- [ ] Set APP_ENV=staging in .env
- [ ] Test all critical flows:
  - [ ] User login/logout
  - [ ] Product CRUD operations
  - [ ] Category management
  - [ ] Invoice generation
- [ ] Monitor error logs for issues
- [ ] Verify database backups are working
- [ ] Load test with multiple concurrent users

## Production Deployment

- [ ] Set APP_ENV=production in .env
- [ ] Set strong database password
- [ ] Enable HTTPS/SSL certificate
- [ ] Set secure database connection string
- [ ] Create logs directory with restricted permissions:
  ```bash
  mkdir -p app/logs
  chmod 700 app/logs
  ```
- [ ] Verify error logs go to file, not displayed to users
- [ ] Disable debug mode (APP_ENV != development)
- [ ] Set up log rotation (logs grow large over time)
- [ ] Create database backup before deployment
- [ ] Test login one final time
- [ ] Monitor error logs for issues
- [ ] Set up monitoring/alerting

## Post-Deployment

- [ ] Document environment variables used
- [ ] Train team on new security practices
- [ ] Schedule code review for remaining pages
- [ ] Plan for rate limiting implementation
- [ ] Schedule security audit (if required)
- [ ] Document all admin credentials in secure vault

## Rollback Plan (If Needed)

1. Backup current database
2. Restore previous schema.sql
3. Revert code to previous version
4. Update admin password hash back to MD5 (if using old code)

```php
// OLD WAY (don't do this in new code)
$password_hash = md5('admin123');
INSERT INTO tbladmin SET Password='$password_hash';
```

## Performance Considerations

- Prepared statements are slightly slower than direct queries
  - Not noticeable for small-medium projects
  - Can add query caching if needed
- CSRF token generation adds minimal overhead
- Bcrypt password hashing takes ~0.1-0.5s per operation
  - Only happens at login, acceptable
  - Use cost 12 for good balance

## Monitoring & Maintenance

After deployment, monitor:

1. **Error Logs**
   ```bash
   tail -f app/logs/php_errors.log
   ```

2. **Security Events**
   ```bash
   grep "SECURITY\|INFO" app/logs/php_errors.log
   ```

3. **Performance**
   - Query response times
   - Page load times
   - Database connection pool usage

4. **Capacity**
   - Logs directory size (may need rotation)
   - Database size growth
   - User concurrency

## Next Steps (After Deployment)

1. Apply same security pattern to remaining pages
2. Implement rate limiting on login
3. Add two-factor authentication
4. Update Cypress tests with CSRF support
5. Schedule quarterly security audits
6. Plan for infrastructure upgrades if needed

## Support & Escalation

If issues occur:
1. Check logs first: `app/logs/php_errors.log`
2. Verify environment variables are set correctly
3. Test database connection manually
4. Check server PHP version (requires 5.5+)
5. Contact DevOps team for infrastructure issues

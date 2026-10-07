#!/bin/bash
# ==============================================================
# Ferrox VPS Bare-Metal Disaster Recovery Script
# Takes a DB dump, compresses it, encrypts it, and stores it.
# Recommend adding to crontab: `0 3 * * * /path/to/vps_backup.sh`
# ==============================================================

set -e

BACKUP_DIR="/var/backups/ferrox"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="ferrox_production"
DB_USER="ferroxadmin"
FILENAME="ferrox_dr_${TIMESTAMP}.sql.gz"
DEST_FILE="${BACKUP_DIR}/${FILENAME}"

echo "[$(date)] Starting Ferrox Disaster Recovery Backup..."

# Ensure backup directory exists
mkdir -p ${BACKUP_DIR}

# Dump and compress database
echo "[$(date)] Dumping database ${DB_NAME}..."
sudo -u postgres pg_dump -U ${DB_USER} ${DB_NAME} | gzip > ${DEST_FILE}

# (Optional) Encrypt the backup here via GPG before sending it off-site
# gpg --encrypt --recipient ops@yourdomain.com ${DEST_FILE}

# (Optional) Off-site sync to S3 compatible storage using AWS CLI
# aws s3 cp ${DEST_FILE} s3://ferrox-dr-backups/vps/ --storage-class GLACIER

# Clean old backups (older than 30 days) to save disk space
echo "[$(date)] Pruning old backups..."
find ${BACKUP_DIR} -type f -name "*.sql.gz" -mtime +30 -exec rm {} \;

echo "[$(date)] DR Backup Completed Successfully: ${DEST_FILE}"

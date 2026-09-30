# ==============================================================
# GCP Disaster Recovery (DR) Plan for Ferrox
# Cloud SQL Automated Backups & Storage Snapshots
# ==============================================================

# Enable automated backups on the primary instance
resource "google_sql_database_instance" "ferrox_primary_dr" {
  name             = "ferrox-db-primary"
  database_version = "POSTGRES_15"
  region           = "europe-west1"

  settings {
    tier = "db-custom-2-8192"

    backup_configuration {
      enabled                        = true
      start_time                     = "03:00" # 3 AM
      point_in_time_recovery_enabled = true    # PITR to restore to any second
      transaction_log_retention_days = 7
      backup_retention_settings {
        retained_backups = 30 # Keep daily backups for 30 days
      }
    }
  }
}

# Cross-Region Read Replica (can be promoted in case of Region failure)
resource "google_sql_database_instance" "ferrox_dr_replica" {
  name                 = "ferrox-db-dr-replica"
  master_instance_name = google_sql_database_instance.ferrox_primary_dr.name
  region               = "europe-west4" # Different Region for DR

  settings {
    tier = "db-custom-2-8192"
  }
}

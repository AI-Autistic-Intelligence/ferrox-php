# Google Cloud Platform IaC for Ferrox PHP (Cloud Run + Cloud SQL)
provider "google" {
  project = var.gcp_project
  region  = var.gcp_region
}

# Cloud Run Service (Ideal for Ferrox HTTP APIs scaling to zero or handling massive bursts)
resource "google_cloud_run_service" "ferrox_service" {
  name     = "ferrox-php-api"
  location = var.gcp_region

  template {
    spec {
      containers {
        image = "gcr.io/${var.gcp_project}/ferrox-php:latest"
        ports {
          container_port = 8080
        }
        env {
          name  = "APP_ENV"
          value = "production"
        }
        env {
          name  = "DB_DSN"
          value = "mysql:host=${google_sql_database_instance.ferrox_db.private_ip_address};dbname=ferrox"
        }
      }
      # Allocate concurrency to match RoadRunner threads
      container_concurrency = 80
    }
  }
}

# Cloud SQL Database (MySQL 8)
resource "google_sql_database_instance" "ferrox_db" {
  name             = "ferrox-cloudsql-mysql"
  database_version = "MYSQL_8_0"
  region           = var.gcp_region

  settings {
    tier = "db-custom-2-8192" # 2 vCPU, 8GB RAM
    backup_configuration {
      enabled    = true
      start_time = "03:00"
    }
  }
}

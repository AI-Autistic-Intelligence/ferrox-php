provider "google" {
  project = var.gcp_project_id
  region  = var.gcp_region
}

# 1. Ferrox Network Backbone (VPC & Subnets)
resource "google_compute_network" "ferrox_vpc" {
  name                    = "ferrox-vpc"
  auto_create_subnetworks = false
}

resource "google_compute_subnetwork" "ferrox_subnet" {
  name          = "ferrox-subnet"
  ip_cidr_range = "10.0.0.0/16"
  region        = var.gcp_region
  network       = google_compute_network.ferrox_vpc.id
}

# 2. GCP GKE (Kubernetes) Cluster for Ferrox Microservices
resource "google_container_cluster" "ferrox_cluster" {
  name     = "ferrox-cluster"
  location = var.gcp_region
  network  = google_compute_network.ferrox_vpc.id
  subnetwork = google_compute_subnetwork.ferrox_subnet.id

  # We can't create a cluster with no node pool defined, but we want to only use
  # separately managed node pools. So we create the smallest possible default
  # node pool and immediately delete it.
  remove_default_node_pool = true
  initial_node_count       = 1
}

resource "google_container_node_pool" "ferrox_nodes" {
  name       = "ferrox-node-pool"
  location   = var.gcp_region
  cluster    = google_container_cluster.ferrox_cluster.name
  node_count = 2

  node_config {
    machine_type = "e2-medium"
    oauth_scopes = [
      "https://www.googleapis.com/auth/cloud-platform"
    ]
  }

  autoscaling {
    min_node_count = 2
    max_node_count = 10
  }
}

# 3. GCP Cloud SQL PostgreSQL (Master & Replica Setup for ReplicaAwareManager)
resource "google_sql_database_instance" "ferrox_master" {
  name             = "ferrox-master"
  database_version = "POSTGRES_15"
  region           = var.gcp_region

  settings {
    tier = "db-custom-2-7680" # 2 vCPU, 7.5 GB RAM
  }
}

resource "google_sql_database_instance" "ferrox_replica" {
  name                 = "ferrox-replica-1"
  database_version     = "POSTGRES_15"
  region               = var.gcp_region
  master_instance_name = google_sql_database_instance.ferrox_master.name

  settings {
    tier = "db-custom-2-7680"
  }
  
  replica_configuration {
    failover_target = false
  }
}

variable "gcp_project_id" {
  type = string
}

variable "gcp_region" {
  default = "europe-west1"
}

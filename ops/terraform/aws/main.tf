# AWS Infrastructure as Code for Ferrox PHP (ECS Fargate + RDS Aurora)
provider "aws" {
  region = var.aws_region
}

# ECS Cluster for memory-resident RoadRunner PHP App
resource "aws_ecs_cluster" "ferrox_cluster" {
  name = "ferrox-production-cluster"
}

resource "aws_ecs_task_definition" "ferrox_app" {
  family                   = "ferrox-app-task"
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  cpu                      = "1024"
  memory                   = "2048"

  container_definitions = jsonencode([
    {
      name      = "ferrox-php-container"
      image     = "${var.ecr_repository_url}:latest"
      essential = true
      portMappings = [
        {
          containerPort = 8080
          hostPort      = 8080
        }
      ]
      environment = [
        { name = "APP_ENV", value = "production" },
        { name = "DB_DSN", value = "mysql:host=${aws_rds_cluster.ferrox_db.endpoint};dbname=ferrox" }
      ]
    }
  ])
}

# High-Availability Database
resource "aws_rds_cluster" "ferrox_db" {
  cluster_identifier      = "ferrox-aurora-cluster"
  engine                  = "aurora-mysql"
  engine_version          = "8.0.mysql_aurora.3.04.0"
  database_name           = "ferrox"
  master_username         = "ferrox_admin"
  master_password         = var.db_password
  backup_retention_period = 7
  preferred_backup_window = "02:00-04:00"
}

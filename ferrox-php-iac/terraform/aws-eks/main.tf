provider "aws" {
  region = var.aws_region
}

# 1. Ferrox Network Backbone
module "vpc" {
  source  = "terraform-aws-modules/vpc/aws"
  version = "5.0.0"

  name = "ferrox-vpc"
  cidr = "10.0.0.0/16"

  azs             = ["${var.aws_region}a", "${var.aws_region}b"]
  private_subnets = ["10.0.1.0/24", "10.0.2.0/24"]
  public_subnets  = ["10.0.101.0/24", "10.0.102.0/24"]
  
  enable_nat_gateway = true
}

# 2. AWS EKS (Kubernetes) Cluster for Ferrox Microservices
module "eks" {
  source          = "terraform-aws-modules/eks/aws"
  version         = "19.0.0"
  cluster_name    = "ferrox-cluster"
  cluster_version = "1.27"
  vpc_id          = module.vpc.vpc_id
  subnet_ids      = module.vpc.private_subnets

  eks_managed_node_groups = {
    ferrox_workers = {
      min_size     = 2
      max_size     = 10
      desired_size = 2
      instance_types = ["t3.medium"]
    }
  }
}

# 3. AWS RDS Aurora (Master & Replica Setup for ReplicaAwareManager)
module "aurora" {
  source  = "terraform-aws-modules/rds-aurora/aws"
  name    = "ferrox-aurora-cluster"
  engine  = "aurora-postgresql"
  
  vpc_id  = module.vpc.vpc_id
  subnets = module.vpc.private_subnets

  replica_count = 2 # Proves Ferrox-PHP Read-Your-Writes logic
}

variable "aws_region" {
  default = "eu-west-1"
}

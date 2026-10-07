provider "aws" {
  region = var.aws_region
}

variable "aws_region" {
  default = "eu-west-1"
}

# =========================================================
# 1. Security Groups (Zero-Trust Network)
# =========================================================
resource "aws_security_group" "ferrox_vps_sg" {
  name        = "ferrox-vps-sg"
  description = "Allow HTTP, HTTPS and SSH"

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["YOUR.IP.ADDRESS.HERE/32"] # Restrict SSH to your IP!
  }

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_security_group" "ferrox_rds_sg" {
  name        = "ferrox-rds-sg"
  description = "Allow Postgres access only from the VPS"

  ingress {
    from_port       = 5432
    to_port         = 5432
    protocol        = "tcp"
    security_groups = [aws_security_group.ferrox_vps_sg.id] # Only the VPS can talk to the DB
  }
}

# =========================================================
# 2. Managed Relational Database (RDS PostgreSQL)
# =========================================================
resource "aws_db_instance" "ferrox_postgres" {
  identifier           = "ferrox-production-db"
  allocated_storage    = 20
  engine               = "postgres"
  engine_version       = "15.3"
  instance_class       = "db.t4g.micro"
  username             = "ferroxadmin"
  password             = "CHANGE_ME_IN_PROD_USE_AWS_SECRETS" # Better: use aws_secretsmanager
  skip_final_snapshot  = true
  publicly_accessible  = false # Protected inside AWS VPC
  vpc_security_group_ids = [aws_security_group.ferrox_rds_sg.id]
}

# =========================================================
# 3. Bare-Metal VPS (EC2 Instance)
# =========================================================
data "aws_ami" "ubuntu" {
  most_recent = true
  owners      = ["099720109477"] # Canonical
  
  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-jammy-22.04-amd64-server-*"]
  }
}

resource "aws_instance" "ferrox_vps" {
  ami           = data.aws_ami.ubuntu.id
  instance_type = "t3.small"
  
  vpc_security_group_ids = [aws_security_group.ferrox_vps_sg.id]
  
  # Inject our VPS deploy script via User Data (cloud-init)
  user_data = file("../../vps/deploy.sh")

  tags = {
    Name = "Ferrox-Production-VPS"
  }
}

output "vps_public_ip" {
  value = aws_instance.ferrox_vps.public_ip
}

output "rds_endpoint" {
  value = aws_db_instance.ferrox_postgres.endpoint
}

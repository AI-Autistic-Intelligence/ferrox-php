# Ferrox Zero-Trust IAM Configuration
# Creates a dedicated IAM Role for EKS Pods (IRSA) to access AWS resources.

module "vpc_cni_irsa" {
  source  = "terraform-aws-modules/iam/aws//modules/iam-role-for-service-accounts-eks"
  version = "~> 5.0"

  role_name_prefix      = "ferrox-vpc-cni-"
  attach_vpc_cni_policy = true
  vpc_cni_enable_ipv4   = true

  oidc_providers = {
    main = {
      provider_arn               = module.eks.oidc_provider_arn
      namespace_service_accounts = ["kube-system:aws-node"]
    }
  }
}

# IAM Role for Ferrox Application to read Secrets from AWS Secrets Manager securely
resource "aws_iam_role" "ferrox_app_role" {
  name = "ferrox-app-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRoleWithWebIdentity"
        Effect = "Allow"
        Principal = {
          Federated = module.eks.oidc_provider_arn
        }
        Condition = {
          StringEquals = {
            "${module.eks.oidc_provider}:sub" = "system:serviceaccount:default:ferrox-app"
          }
        }
      }
    ]
  })
}

# Least-Privilege Policy: Allow Ferrox to read ONLY its own secrets
resource "aws_iam_role_policy" "ferrox_secrets_policy" {
  name = "ferrox-secrets-policy"
  role = aws_iam_role.ferrox_app_role.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Action = [
          "secretsmanager:GetSecretValue",
          "dynamodb:PutItem",
          "dynamodb:GetItem",
          "dynamodb:Scan",
          "dynamodb:Query",
          "dynamodb:UpdateItem"
        ]
        Resource = [
          "arn:aws:secretsmanager:${var.aws_region}:*:secret:ferrox-*",
          aws_dynamodb_table.ferrox_event_store.arn
        ]
      }
    ]
  })
}

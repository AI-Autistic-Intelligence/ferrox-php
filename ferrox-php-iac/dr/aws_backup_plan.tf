# ==============================================================
# AWS Disaster Recovery (DR) Plan for Ferrox
# Uses AWS Backup to automate snapshots across multiple regions
# ==============================================================

resource "aws_backup_vault" "ferrox_dr_vault" {
  name        = "ferrox-dr-vault"
  kms_key_arn = aws_kms_key.dr_key.arn
}

resource "aws_backup_plan" "ferrox_dr_plan" {
  name = "ferrox-dr-daily-backup"

  rule {
    rule_name         = "Daily-Snapshot-30-Days"
    target_vault_name = aws_backup_vault.ferrox_dr_vault.name
    schedule          = "cron(0 3 * * ? *)" # 3 AM UTC Every day
    
    lifecycle {
      delete_after = 30 # Retain for 30 days
    }

    # Cross-Region DR Copy
    copy_action {
      destination_vault_arn = "arn:aws:backup:eu-central-1:${data.aws_caller_identity.current.account_id}:backup-vault:ferrox-dr-vault-replica"
      
      lifecycle {
        delete_after = 30
      }
    }
  }
}

resource "aws_backup_selection" "ferrox_resources" {
  iam_role_arn = aws_iam_role.backup_role.arn
  name         = "ferrox-dr-selection"
  plan_id      = aws_backup_plan.ferrox_dr_plan.id

  # Automatically backup everything tagged with 'System = Ferrox'
  selection_tag {
    type  = "STRINGEQUALS"
    key   = "System"
    value = "Ferrox"
  }
}

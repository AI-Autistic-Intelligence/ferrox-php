# High-Performance NoSQL Database for Ferrox Event Sourcing or Caching

resource "aws_dynamodb_table" "ferrox_event_store" {
  name           = "ferrox-event-store"
  billing_mode   = "PAY_PER_REQUEST" # Serverless billing, scales to zero
  hash_key       = "AggregateId"
  range_key      = "EventVersion"
  
  # Point-in-time recovery for disaster recovery
  point_in_time_recovery {
    enabled = true
  }

  attribute {
    name = "AggregateId"
    type = "S"
  }

  attribute {
    name = "EventVersion"
    type = "N"
  }

  # Encryption at rest using AWS KMS
  server_side_encryption {
    enabled = true
  }

  tags = {
    Environment = "Production"
    System      = "Ferrox"
  }
}

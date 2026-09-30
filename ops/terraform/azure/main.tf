# Azure Infrastructure as Code for Ferrox PHP
provider "azurerm" {
  features {}
}

resource "azurerm_resource_group" "ferrox_rg" {
  name     = "ferrox-production-rg"
  location = "West Europe"
}

# Azure Container Apps (Serverless containers built for Microservices)
resource "azurerm_container_app_environment" "ferrox_env" {
  name                = "ferrox-env"
  location            = azurerm_resource_group.ferrox_rg.location
  resource_group_name = azurerm_resource_group.ferrox_rg.name
}

resource "azurerm_container_app" "ferrox_app" {
  name                         = "ferrox-php-api"
  container_app_environment_id = azurerm_container_app_environment.ferrox_env.id
  resource_group_name          = azurerm_resource_group.ferrox_rg.name
  revision_mode                = "Single"

  template {
    container {
      name   = "ferrox-php"
      image  = "${var.acr_server}/ferrox-php:latest"
      cpu    = 1.0
      memory = "2.0Gi"
      
      env {
        name  = "APP_ENV"
        value = "production"
      }
      env {
        name  = "DB_DSN"
        value = "mysql:host=${azurerm_mysql_flexible_server.ferrox_db.fqdn};dbname=ferrox"
      }
    }
  }
}

# Azure Database for MySQL Flexible Server
resource "azurerm_mysql_flexible_server" "ferrox_db" {
  name                   = "ferrox-mysql-flex"
  resource_group_name    = azurerm_resource_group.ferrox_rg.name
  location               = azurerm_resource_group.ferrox_rg.location
  administrator_login    = "ferrox_admin"
  administrator_password = var.db_password
  sku_name               = "B_Standard_B2s"
  version                = "8.0.21"
}

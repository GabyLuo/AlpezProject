<template>
  <q-layout view="hHh lpR fFf">
    <div class="row boder-panel">
      <q-header elevated class="bg-green-6">
        <q-toolbar>
          <div class="pull-left">
            <q-btn
              flat
              dense
              round
              @click="leftDrawerOpen = !leftDrawerOpen"
              aria-label="Menu"
              class="color-white">
              <q-icon name="menu" />
            </q-btn>
          </div>
          <div style="margin-left:12px" class="col-sm-10">
           <img style="margin-top: 6px;" height="25px;" src="../assets/logo.png" alt="Alpez">
          </div>
          <div class="col-sm-2 col-sm-2 pull-right" style="pad">
            <template>
              <q-btn-dropdown class="color-white" flat :label="`${nickname}`" style="margin-right: 10%">
                <q-list>
                  <q-item clickable v-close-popup @click="openProfile()">
                    <q-item-section>
                      <q-item-label>Perfil</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="logOut()">
                    <q-item-section>
                      <q-item-label>Cerrar sesión</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </template>
          </div>
        </q-toolbar>
      </q-header>
    </div>

    <q-drawer
      v-model="leftDrawerOpen"
      bordered
      content-class="bg-white"
      :width="256"
      :breakpoint="700"
      :mini="false">
      <q-list>
        <q-expansion-item
          expand-separator
          label="Dashboard"
          v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 20 || roleId === 23 || roleId === 2 || roleId === 22 || roleId === 27 || roleId === 29 || roleId === 28 || roleId === 17 || roleId === 26 || roleId === 2 || roleId === 26">
          <q-item to="/dashboard-inventory">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Inventario</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/warnings"  v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 23 || roleId === 20 || roleId === 22 || roleId === 27 || roleId === 29 || roleId === 28 || roleId === 17">
            <q-item-section avatar>
              <q-icon name="receipt_long" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Compras</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/history" v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 20 || roleId === 17 || roleId === 22 || roleId === 28 || roleId === 29 || roleId === 17 || roleId === 27">
            <q-item-section avatar>
              <q-icon name="point_of_sale" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Ventas</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/comparatives" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="addchart" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Distribución</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/trends" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="fas fa-donate" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Finanzas</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Ventas"
          v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 20 || roleId === 23 || roleId === 22 || roleId === 27 || roleId === 29 || roleId === 28 || roleId === 17">
          <q-item to="/shopping-carts">
            <q-item-section avatar>
              <q-icon name="fas fa-shopping-cart" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Pedidos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/storage-exits"  v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 23 || roleId === 20 || roleId === 22 || roleId === 27 || roleId === 29 || roleId === 28 || roleId === 17">
            <q-item-section avatar>
              <q-icon name="fact_check" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Remisiones</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/customers" v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 20 || roleId === 17 || roleId === 22 || roleId === 28 || roleId === 29 || roleId === 17 || roleId === 27">
            <q-item-section avatar>
              <q-icon name="fas fa-shopping-cart" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Clientes</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/trips" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="travel_explore" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Embarques</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/prices-list" v-if="roleId === 1 || roleId === 3 || roleId === 20 || roleId === 25 || roleId === 4 || roleId === 22 || roleId === 27 || roleId === 29">
            <q-item-section avatar>
              <q-icon name="fas fa-dollar-sign" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Lista de Precios</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/carriers" v-if="roleId === 1 || roleId === 3 || roleId === 7" >
            <q-item-section avatar>
              <q-icon name="fas fa-truck" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Paqueterías</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Tesorería"
          v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 22 || roleId === 28">
          <q-item to="/payments">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Cuentas por cobrar</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/forecast">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Forecast Abonos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/debts-to-pay">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Cuentas por pagar</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/forecast-debst">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Forecast cuentas por pagar</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Compras"
          v-if="roleId === 1 || roleId === 21 || roleId === 22 || roleId === 20 || roleId === 28">
          <q-item to="/purchase-orders" v-if="roleId === 1 || roleId === 21 || roleId === 22 || roleId === 26 || roleId === 20 || roleId === 28">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Ordenes</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/suppliers" v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 22 || roleId === 20 || roleId === 17 || roleId === 27 || roleId === 28 || roleId === 29">
            <q-item-section avatar>
              <q-icon name="fas fa-building" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Proveedores</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Reportes"
          v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4 || roleId === 29 || roleId === 22 || roleId === 20">
          <q-item to="/close-sale-remission" v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Corte de caja remisión</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/close-sale-loan" v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4 || roleId === 29 || roleId === 22">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Pedidos por prestamo</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/old-balance" v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4 || roleId === 22 || roleId === 28">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Antigüedad de saldos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/reports/shopping" v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4 || roleId === 22 || roleId === 28">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Compras proveedor</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/reports/shoppingsuppliers" v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4 || roleId === 22 || roleId === 28">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Compras de proveedor</q-item-label>
            </q-item-section>
          </q-item>
          <!--<q-item to="/auxiliary-accountant"  v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 23 || roleId === 2 || roleId === 17 || roleId === 28">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Pedidos</q-item-label>
            </q-item-section>
          </q-item>-->
          <q-item to="/close-sale" v-if="roleId === 1 || roleId === 17 || roleId === 18 || roleId === 3 || roleId === 28 || roleId === 4">
            <q-item-section avatar>
              <q-icon name="fas fa-clipboard-list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Corte de caja</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/auxiliary-accountant"  v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 23 || roleId === 2 || roleId === 17 || roleId === 28">
            <q-item-section avatar>
              <q-icon name="fas fa-file-pdf" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Auxiliar Contable</q-item-label>
            </q-item-section>
          </q-item>
                    <q-item to="/shopping-carts-reports"  v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 23 || roleId === 2 || roleId === 17 || roleId === 28 || roleId === 20 || roleId === 28 || roleId === 22 || roleId === 29">
            <q-item-section avatar>
              <q-icon name="fas fa-file-pdf" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Reportes</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Inventarios"
          v-if="roleId === 1 || roleId === 3 || roleId === 2 || roleId === 7 || roleId === 20 || roleId === 22 || roleId === 26">
          <q-item to="/movements" v-if="roleId === 1 || roleId === 3 || roleId === 2 || roleId === 7 || roleId === 20 || roleId === 22 || roleId === 26">
            <q-item-section avatar>
              <q-icon name="fas fa-cart-plus" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Movimientos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/raw-material-shipments" v-if="roleId === 1 || roleId === 3 || roleId === 2 || roleId === 7 || roleId === 20 || roleId === 22 || roleId === 26">
            <q-item-section avatar>
              <q-icon name="fas fa-dolly" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Recepciones</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/storage-inventory" v-if="roleId === 1 || roleId === 3 || roleId === 2 || roleId === 7 || roleId === 20 || roleId === 22 || roleId === 26">
            <q-item-section avatar>
              <q-icon name="fas fa-warehouse" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Existencias</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/stock-minimal" v-if="roleId === 1 || roleId === 3 || roleId === 2 || roleId === 7 || roleId === 20 || roleId === 22 || roleId === 26">
            <q-item-section avatar>
              <q-icon name="fas fa-warehouse" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Stock Minimo</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/kardex" v-if="roleId === 1 || roleId === 3 || roleId === 2 || roleId === 7 || roleId === 20 || roleId === 22 || roleId === 26">
            <q-item-section avatar>
              <q-icon name="list" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Kardex</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/products" v-if="roleId === 1 || roleId === 3 || roleId === 22">
            <q-item-section avatar>
              <q-icon name="emoji_objects" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Artículos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/categories" v-if="roleId === 1 || roleId === 3 || roleId === 22">
            <q-item-section avatar>
              <q-icon name="fas fa-cubes" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Categorías</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/lines" v-if="roleId === 1 || roleId === 3 || roleId === 22">
            <q-item-section avatar>
              <q-icon name="fas fa-grip-lines-vertical" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Subcategorías</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/marks" v-if="roleId === 1 || roleId === 3 || roleId === 22">
            <q-item-section avatar>
              <q-icon name="fas fa-grip-lines-vertical" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Marcas</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/units" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-prescription-bottle" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Unidades</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/storages" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-warehouse" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Almacenes</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/storage-types" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-warehouse" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Tipos de Almacén</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Logística"
          v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8"
          v-show="false">
          <q-item to="/ranges" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="timeline" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Destinos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/state" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="location_city" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Estados</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/municipality" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="home_work" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Municipios</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/expenses" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="price_check" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Tipo de gastos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/vehicle-types" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="commute" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Tipos de Vehículos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/vehicle" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="local_gas_station" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Vehículos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/drivers" v-if="roleId === 1 || roleId === 3  || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="fas fa-truck" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Choferes</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/trips" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="travel_explore" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Embarques</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Catálogos"
          v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 22 || roleId === 20 || roleId === 17 || roleId === 27 || roleId === 28 || roleId === 29">
          <q-item to="/branch-offices" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-store-alt" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Estaciones</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/channels" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-comment-dots" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Canales</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/currencies" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-dollar-sign" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Tipo de Moneda</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/vehicle" v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="local_gas_station" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Vehículos</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/drivers" v-if="roleId === 1 || roleId === 3  || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="fas fa-truck" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Choferes</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/supercluster" v-if="roleId === 1 || roleId === 3  || roleId === 7 || roleId === 8">
            <q-item-section avatar>
              <q-icon name="fas fa-globe" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Clusters</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
        <q-expansion-item
          expand-separator
          label="Sistema"
          v-if="roleId === 1 || roleId === 3">
          <q-item to="/users">
            <q-item-section avatar>
              <q-icon name="supervisor_account" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Usuarios</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/roles">
            <q-item-section avatar>
              <q-icon name="supervisor_account" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Roles</q-item-label>
            </q-item-section>
          </q-item>
          <q-item to="/actions" v-if="roleId === 1 || roleId === 3">
            <q-item-section avatar>
              <q-icon name="fas fa-cog" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Configuración</q-item-label>
            </q-item-section>
          </q-item>
        </q-expansion-item>
      </q-list>
    </q-drawer>

    <q-page-container style="background-color: #0e0f1f;">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { openURL } from 'quasar'

export default {
  name: 'MainLayout',
  data () {
    return {
      leftDrawerOpen: (this.$q.platform.is.desktop && this.$router.currentRoute.path !== '/' && this.$router.currentRoute.path !== '/shopping-cart')
    }
  },
  computed: {
    nickname () {
      return this.$store.getters['users/nickname']
    },
    roles () {
      return this.$store.getters['users/roles']
    },
    repositories () {
      return this.$store.getters['users/repositories']
    },
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    }
  },
  methods: {
    openURL,
    openProfile () {
      this.$router.push('/profile')
    },
    openRep (url) {
      this.$router.push(url)
    },
    logOut () {
      localStorage.removeItem('JWT')
      window.location.reload()
    }
  }
}
</script>

<style>
</style>

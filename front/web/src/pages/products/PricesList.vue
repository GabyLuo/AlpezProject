<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Lista de Precios" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-8 col-md-8 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn v-if="roleId !== 20 && roleId !== 4 && roleId !== 29" color="positive" icon="cloud_download" @click="generateCsvPrices()">
              <q-tooltip content-class="bg-primary">Descargar Precios</q-tooltip>
            </q-btn>
            <q-btn v-if="roleId !== 20 && roleId !== 4 && roleId !== 29" color="blue" icon="cloud_upload" label="" @click="openUploadFileModalPrices()">
              <q-tooltip content-class="bg-primary">Actualizar Precios</q-tooltip>
            </q-btn>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-md-3" style="padding: 3px">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="category"
                :options="categoryOptions"
                label="Categorías"
                @input="fetchForCategory()"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cubes" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="line"
                :options="filteredLineOptions"
                label="Subcategoría"
                @input="fetchFromServer()"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-grip-lines-vertical" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px">
              <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="mark"
                          label="Marcas"
                          :options="options"
                          use-input
                          hide-selected
                          fill-input
                          @filter="filterMarcas"
                          @input="fetchFromServer()"
                          >
                          <template v-slot:prepend>
                        <q-icon name="fas fa-grip-lines-vertical" />
                        </template>
                        </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="status"
                :options="[
                  {value: true, label: 'ACTIVO'},
                  {value: false, label: 'INACTIVO'}
                ]"
                label="Estatus"
                @input="fetchForStatus()"
              >
                <template v-slot:prepend>
                  <q-icon :name="(status.value ? 'battery_full' : 'battery_alert')" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
          <q-input
            color="dark"
            bg-color="secondary"
            filled
            v-model="description"
            label="Nombre"
            @keyup.enter="fetchFromServer()"
          >
          <template v-slot:prepend>
            <q-icon name="fingerprint" />
          </template>
        </q-input>
      </div>
      <div class="col-xs-12 col-md-12 pull-right" style="padding: 3px;">
              <q-btn color="primary" icon="fas fa-search" @click="fetchFromServer()" style="height: 96%;" />
            </div>
          </div>
          <q-table
          v-if="roleId === 1"
            flat
            bordered
            :data="products"
            :columns="columnsAdmin"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
          v-if="(roleId !== 1 && roleId !== 4 &&  roleId !== 27 && roleId !== 20 && roleId !== 29) && branchId === 9"
            flat
            bordered
            :data="products"
            :columns="columns"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="(roleId !== 1 && roleId === 4 || roleId === 27) && branchId === 9"
            flat
            bordered
            :data="products"
            :columns="columnscaja"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <!-- Tabla Rol de ventas -->
          <!-- Se quito de rebasa_code class="cursor-pointer"  text-decoration:underline;  @click.native="editSelectedRow(props.row.id)"-->
          <q-table
           v-if="roleId !== 1 && roleId === 20 && branchId === 9"
            flat
            bordered
            :data="products"
            :columns="columnsvendedor"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="roleId !== 1 && roleId === 29 && branchId === 9"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorcampo"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>

          <q-table
          v-if="(roleId !== 1 && roleId !== 4 &&  roleId !== 27 && roleId !== 20 && roleId !== 29) && branchId === 12"
            flat
            bordered
            :data="products"
            :columns="columnslara"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="(roleId !== 1 && roleId === 4 || roleId === 27) && branchId === 12"
            flat
            bordered
            :data="products"
            :columns="columnscajalara"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <!-- Tabla Rol de ventas -->
          <!-- Se quito de rebasa_code class="cursor-pointer"  text-decoration:underline;  @click.native="editSelectedRow(props.row.id)"-->
          <q-table
           v-if="roleId !== 1 && roleId === 20 && branchId === 12"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorlara"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="roleId !== 1 && roleId === 29 && branchId === 12"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorcampolara"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>

          <q-table
          v-if="(roleId !== 1 && roleId !== 4 &&  roleId !== 27 && roleId !== 20 && roleId !== 29) && branchId === 13"
            flat
            bordered
            :data="products"
            :columns="columnsrodamiento"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="(roleId !== 1 && roleId === 4 || roleId === 27) && branchId === 13"
            flat
            bordered
            :data="products"
            :columns="columnscajarodamiento"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <!-- Tabla Rol de ventas -->
          <!-- Se quito de rebasa_code class="cursor-pointer"  text-decoration:underline;  @click.native="editSelectedRow(props.row.id)"-->
          <q-table
           v-if="roleId !== 1 && roleId === 20 && branchId === 13"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorrodamiento"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="roleId !== 1 && roleId === 29 && branchId === 13"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorcamporodamiento"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>

          <q-table
          v-if="(roleId !== 1 && roleId !== 4 &&  roleId !== 27 && roleId !== 20 && roleId !== 29) && branchId === 14"
            flat
            bordered
            :data="products"
            :columns="columnssalle"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="(roleId !== 1 && roleId === 4 || roleId === 27) && branchId === 14"
            flat
            bordered
            :data="products"
            :columns="columnscajasalle"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <!-- Tabla Rol de ventas -->
          <!-- Se quito de rebasa_code class="cursor-pointer"  text-decoration:underline;  @click.native="editSelectedRow(props.row.id)"-->
          <q-table
           v-if="roleId !== 1 && roleId === 20 && branchId === 14"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorsalle"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciabodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_a" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.price_a)}` }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
          <q-table
           v-if="roleId !== 1 && roleId === 29 && branchId === 14"
            flat
            bordered
            :data="products"
            :columns="columnsvendedorcamposalle"
            row-key="old_code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <!--<q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="cursor-pointer" style="text-align: center; text-decoration:underline;" :props="props">{{ props.row.old_code }}</q-td>-->
                <q-td key="rebasa_code" flat style="text-align: center;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <!--<q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>-->
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="mark" style="text-align: left;" :props="props">{{ props.row.mark }}</q-td>
                <q-td key="existenciabodega" style="text-align: right;" :props="props">{{ props.row.existenciaBbodega }}</q-td>
                <q-td key="existenciarebasa" style="text-align: right;" :props="props">{{ props.row.existenciarebasa }}</q-td>
                <q-td key="existenciamangueras" style="text-align: right;" :props="props">{{ props.row.existenciamangueras }}</q-td>
                <q-td key="existenciasalle" style="text-align: right;" :props="props">{{ props.row.existenciasalle }}</q-td>
                <q-td key="existenciarodamientos" style="text-align: right;" :props="props">{{ props.row.existenciarodamientos }}</q-td>
                <q-td key="price_b" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_b)}` }}</q-td>
                <q-td key="price_c" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_c)}` }}</q-td>
                <q-td key="price_d" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_d)}` }}</q-td>
                <q-td key="price_e" style="text-align: right;" :props="props">{{  `${currencyFormatter.format(props.row.price_e)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>
    <q-dialog v-model="documentFileModalUpdatePrices" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentNamePrices }}</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrlPrices"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRefPrices"
              hide-upload-btn
              @uploaded="afterUploadDocumentFilePrices"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFilePrices()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'IndexProductsPrices',
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      category: { value: null, label: 'TODOS' },
      line: { value: null, label: 'TODOS' },
      status: { value: true, label: 'ACTIVO' },
      categoryOptions: [],
      lineOptions: [],
      description: null,
      pagination: {
        sortBy: 'code',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCIÓN', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsAdmin: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCIÓN', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedor: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorcampo: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnscaja: [
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnslara: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorlara: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorcampolara: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnscajalara: [
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsrodamiento: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorrodamiento: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorcamporodamiento: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnscajarodamiento: [
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnssalle: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorsalle: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_a', align: 'center', label: 'PRECIO A', field: 'pricea', style: 'width: 15%', sortable: false },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnsvendedorcamposalle: [
        // { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_b', align: 'center', label: 'PRECIO B', field: 'priceb', style: 'width: 10%', sortable: false },
        { name: 'price_c', align: 'center', label: 'PRECIO C', field: 'pricec', style: 'width: 10%', sortable: false },
        { name: 'price_d', align: 'center', label: 'PRECIO D', field: 'priced', style: 'width: 10%', sortable: false },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      columnscajasalle: [
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO REBASA', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        // { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCION', field: 'description', style: 'width: 10%', sortable: true },
        { name: 'mark', align: 'center', label: 'MARCA', field: 'mark', style: 'width: 15%', sortable: true },
        { name: 'existenciabodega', align: 'center', label: 'BODEGA', field: 'existenciabodega', style: 'width: 15%', sortable: true },
        { name: 'existenciarebasa', align: 'center', label: 'REBASA 20', field: 'existenciarebasa', style: 'width: 15%', sortable: true },
        { name: 'existenciamangueras', align: 'center', label: 'MANGUERAS', field: 'existenciamangueras', style: 'width: 15%', sortable: true },
        { name: 'existenciasalle', align: 'center', label: 'LA SALLE', field: 'existenciasalle', style: 'width: 15%', sortable: true },
        { name: 'existenciarodamientos', align: 'center', label: 'RODAMIENTOS', field: 'existenciarodamientos', style: 'width: 15%', sortable: true },
        { name: 'price_e', align: 'center', label: 'PRECIO MOSTRADOR', field: 'pricee', style: 'width: 10%', sortable: false }
      ],
      products: [],
      filter: '',
      photoModal: false,
      photoUrl: null,
      photoProductId: null,
      photoProductCode: null,
      auxCaja: 0,
      options: this.markOptions,
      mark: null,
      markOptions: [],
      documentFileModalUpdatePrices: false,
      documentNamePrices: null,
      serverUrl: process.env.API,
      controller: null
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(20) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 29) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.getCategories()
    this.getLines()
    this.fetchFromServer()
    this.getMarks()
  },
  methods: {
    PadLeft (value, length) {
      return (value.toString().length < length) ? this.PadLeft('0' + value, length) : value
    },
    fetchForCategory () {
      this.line = { label: 'TODOS', value: null }
      this.fetchFromServer()
    },
    fetchFromServer () {
      // const propiedades = this.$store.getters['users/branch']
      // console.log(propiedades)
      // const user = this.$store.getters['users/rol']
      // console.log(user)
      /* if (this.$store.getters['users/roles'].includes(4)) {
        this.auxCaja = 4
      }
      if (this.$store.getters['users/roles'].includes(27)) {
        this.auxCaja = 27
      }
      if (this.$store.getters['users/roles'].includes(20)) {
        this.auxCaja = 20
      } */
      // console.log(this.category)
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    async qTableRequest (props) {
      this.$q.loading.show()
      this.pagination = props.pagination
      this.filter = props.filter
      this.products = []
      const params = []
      if (this.category.value !== null || this.category.value !== 'TODOS') {
        params.category = 20
      } else {
        params.category = this.category.value
      }
      params.line = this.line.value
      params.status = this.status.value
      params.pagination = this.pagination
      params.filter = this.filter
      params.mark = this.mark
      params.description = this.description
      // console.log(params)
      if (this.controller) {
        api.cancel()
      }
      this.controller = api.post('/products/pagPrices', params).then(({ data }) => {
        this.$q.loading.hide()
        this.products = data.prices
        this.pagination.rowsNumber = data.productsCount
        this.controller = null
      }).catch(error => error)
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
        this.categoryOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    getLines () {
      api.get('/lines/options').then(({ data }) => {
        this.lineOptions = data.options
        this.lineOptions.unshift({ value: null, label: 'TODOS' })
        this.$q.loading.hide()
      })
    },
    fetchForStatus () {
      this.fetchFromServer()
    },
    editSelectedRow (id) {
      this.$router.push(`/products/${id}`)
    },
    filterMarcas (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.markOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getMarks () {
      this.mark = { label: 'TODOS', value: null }
      api.get('/marks/options').then(({ data }) => {
        this.markOptions = data.options
        this.markOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    generateCsv () {
      let categoryId = 'TODOS'
      let lineId = 'TODOS'
      if (this.category) { categoryId = Number(this.category.value) }
      if (this.line) { lineId = Number(this.line.value) }
      const uri = process.env.API + `products/csv/${categoryId}/${lineId}`
      window.open(uri, '_blank')
    },
    generateCsvPrices () {
      let categoryId = 'TODOS'
      let lineId = 'TODOS'
      let markId = 'TODOS'
      if (this.category) { categoryId = Number(this.category.value) }
      if (this.line) { lineId = Number(this.line.value) }
      if (this.mark) { markId = Number(this.mark.value) }
      const uri = process.env.API + `products/csvprices/${categoryId}/${lineId}/${markId}`
      window.open(uri, '_blank')
    },
    openUploadFileModalPrices () {
      this.documentFileModalUpdatePrices = true
    },
    uploadDocumentFilePrices () {
      this.$refs.fileDocumentRefPrices.upload()
    },
    fileDocumentUrlPrices () {
      // const id = this.$route.params.id
      return `${process.env.API}products-prices/filePrices`
    },
    afterUploadDocumentFilePrices (response) {
      // console.log(response)
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModalUpdatePrices = false
      }
    }
  },
  computed: {
    token () {
      const token = 'Bearer ' + localStorage.getItem('JWT')
      return token
    },
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    branchId () {
      const branch = this.$store.getters['users/branch']
      return parseInt(branch)
    },
    filteredLineOptions () {
      if (this.category != null && this.category.value != null) {
        return this.lineOptions.filter(l => Number(l.category) === Number(this.category.value))
      }
      return this.lineOptions
    },
    filteredProducts () {
      // console.log(this.products)
      const products = this.products.filter(product => product.active === this.status.value)
      if (this.line != null && this.line.value != null) {
        return products.filter(product => Number(product.line_id) === Number(this.line.value))
      } else if (this.category != null && this.category.value != null) {
        return products.filter(product => Number(product.category_id) === Number(this.category.value))
      }
      return products
    }
  }
}
</script>

<style>
</style>

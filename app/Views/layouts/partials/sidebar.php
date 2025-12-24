<?php $uri = service('uri'); ?>
<!-- 
    [SARAN UI/UX] Sidebar dibuat hoverable pada layar besar (lgAndUp) dan menjadi drawer sementara pada layar kecil.
    Pastikan untuk menginisialisasi 'mini: true' di data Vue Anda (misal: di file backend-core.js).
-->
<v-navigation-drawer
    color="blue-grey darken-3"
    dark
    v-model="sidebarMenu"
    app
    floating
    :permanent="$vuetify.breakpoint.lgAndUp"
    :mini-variant.sync="mini"
    @mouseenter="mini = false"
    @mouseleave="mini = true"
    class="elevation-3"
>
    <v-list-item class="px-2 pt-1">
        <v-list-item-avatar><v-img src="<?= $logo_url ?>" alt="<?= $app_name ?>"></v-img></v-list-item-avatar>
        <v-list-item-title class="text-h6 font-weight-light">TRON</v-list-item-title>
    </v-list-item>
    <v-divider></v-divider>

    <v-list nav dense> 
        <v-list-item link href="<?= base_url('display'); ?>" target="_blank">
            <v-list-item-icon><v-icon>mdi-arrow-right</v-icon></v-list-item-icon>
            <v-list-item-content><v-list-item-title>Tampil</v-list-item-title></v-list-item-content>
        </v-list-item>

        <v-list-item link href="<?= base_url('dashboard'); ?>" <?= $uri->getSegment(1) == "dashboard" ? 'class="v-item--active v-list-item--active"' : '' ?>>
            <v-list-item-icon><v-icon>mdi-home</v-icon></v-list-item-icon>
            <v-list-item-content><v-list-item-title>Dashboard</v-list-item-title></v-list-item-content>
        </v-list-item>

        <?php if (session()->get('user_type') == 1) : ?>
            <v-list-group :value="false" prepend-icon="mdi-monitor-dashboard">
                <template v-slot:activator><v-list-item-content><v-list-item-title>Display</v-list-item-title></v-list-item-content></template>
                <v-list-item link href="<?= base_url('news'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-newspaper</v-icon></v-list-item-icon><v-list-item-title>News</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('agenda'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-calendar</v-icon></v-list-item-icon><v-list-item-title>Agenda</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('galeri'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-image-multiple</v-icon></v-list-item-icon><v-list-item-title>Galeri</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('video'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-video</v-icon></v-list-item-icon><v-list-item-title>Video</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('cuaca'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-weather-cloudy</v-icon></v-list-item-icon><v-list-item-title>Cuaca</v-list-item-title></v-list-item>
            </v-list-group>
            
            <v-list-group prepend-icon="mdi-mosque">
                <template v-slot:activator><v-list-item-content><v-list-item-title>Masjid</v-list-item-title></v-list-item-content></template>
                <v-list-item link href="<?= base_url('jadwalsholat'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-clock-check-outline</v-icon></v-list-item-icon><v-list-item-title>Jadwal Sholat</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('agamaquotes'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-format-quote-open</v-icon></v-list-item-icon><v-list-item-title>Quotes Agama</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('keuanganmasjid'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-cash-multiple</v-icon></v-list-item-icon><v-list-item-title>Keuangan Masjid</v-list-item-title></v-list-item>
            </v-list-group>

            <v-list-item link href="<?= base_url('user'); ?>">
                <v-list-item-icon><v-icon>mdi-account-multiple</v-icon></v-list-item-icon>
                <v-list-item-content><v-list-item-title>Pengguna</v-list-item-title></v-list-item-content>
            </v-list-item>

            <v-list-item link href="<?= base_url('backup'); ?>">
                <v-list-item-icon><v-icon>mdi-database</v-icon></v-list-item-icon>
                <v-list-item-content><v-list-item-title>Backup DB</v-list-item-title></v-list-item-content>
            </v-list-item>
            
            <v-list-group prepend-icon="mdi-cog">
                <template v-slot:activator><v-list-item-content><v-list-item-title>Pengaturan</v-list-item-title></v-list-item-content></template>
                <v-list-item link href="<?= base_url('setting/general'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-cog-outline</v-icon></v-list-item-icon><v-list-item-title>Umum</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('setting/app'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-application-cog</v-icon></v-list-item-icon><v-list-item-title>Aplikasi</v-list-item-title></v-list-item>
                <v-list-item link href="<?= base_url('setting/admin-sidang'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-key-variant</v-icon></v-list-item-icon><v-list-item-title>OTP Sidang</v-list-item-title></v-list-item>
            </v-list-group>
        <?php endif; ?>
        
        <?php if ((session()->get('user_type') == 2) || (session()->get('user_type') == 3)) : ?>
            <v-list-item link href="<?= base_url('member'); ?>">
                <v-list-item-icon><v-icon>mdi-home</v-icon></v-list-item-icon>
                <v-list-item-content><v-list-item-title>Dashboard</v-list-item-title></v-list-item-content>
            </v-list-item>
        <?php endif; ?>
    </v-list>

     <template v-slot:append>
         <div class="pa-2">
             <div v-if="!mini" class="text-center text-caption">&copy; <?= date('Y') ?> Tron</div>
         </div>
     </template>
</v-navigation-drawer>
<v-app-bar app color="white" light elevation="2">
    <v-app-bar-nav-icon @click.stop="sidebarMenu = !sidebarMenu"></v-app-bar-nav-icon>
    <v-toolbar-title></v-toolbar-title>
    <v-spacer></v-spacer>
    
    <?php if (!empty(session()->get('username'))) : ?>
        <v-menu offset-y left>
            <template v-slot:activator="{ on, attrs }">
                <v-btn text v-bind="attrs" v-on="on" class="font-weight-medium">
                    <v-icon left>mdi-account-circle</v-icon> <?= session()->get('username') ?> <v-icon right>mdi-chevron-down</v-icon>
                </v-btn>
            </template>

            <v-card class="rounded-lg elevation-4" min-width="250">
                <v-list-item class="pa-4">
                    <v-list-item-avatar color="indigo lighten-5">
                        <v-icon color="indigo">mdi-account-circle</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content>
                        <v-list-item-title class="font-weight-bold grey--text text--darken-3">
                            <?= session()->get('username') ?>
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            <v-chip color="teal" small dark class="mt-1">
                                <?= session()->get('user_type') == 1 ? 'ADMIN' : 'USER'; ?>
                            </v-chip>
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
                <v-divider></v-divider>
                <v-list dense>
                    <v-list-item link href="<?= base_url(); ?>">
                        <v-list-item-icon><v-icon color="blue-grey">mdi-home</v-icon></v-list-item-icon>
                        <v-list-item-content><v-list-item-title>Kembali ke Beranda</v-list-item-title></v-list-item-content>
                    </v-list-item>
                    <v-list-item link @click="logoutUser">
                        <v-list-item-icon><v-icon color="red">mdi-logout</v-icon></v-list-item-icon>
                        <v-list-item-content><v-list-item-title>Logout</v-list-item-title></v-list-item-content>
                    </v-list-item>
                </v-list>
            </v-card>
        </v-menu>
    <?php endif; ?>
    
    <v-divider class="mx-1" vertical></v-divider>
    <v-btn icon @click.stop="rightMenu = !rightMenu">
        <v-icon>mdi-cog-outline</v-icon>
    </v-btn>
</v-app-bar>
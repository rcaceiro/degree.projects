<template><div>
    <div>
        <div class="alert alert-success" v-if="showSuccess">
            <button type="button" class="btn btn-link pull-right" v-on:click="showSuccess=false">&times;</button>
            <strong>{{ successMessage }}</strong>
        </div>
        <div class="alert alert-danger" v-if="showFailure">
            <button class="btn btn-link pull-right" @click="showFailure=false">
                &times;
            </button>
            <strong><p v-for="msg in failMessage">{{ msg }}</p></strong>
        </div>
    </div>

    <form>
        <div class="row">
            <div class="col-md-offset-4 col-md-4 col-xs-12">
                <h2 class="form-signin-heading">Edit Platform Configurations</h2>

                <label for="platformEmail">
                    E-mail address:
                </label>
                <input type="text" id="platformEmail"
                       class="form-control"
                       placeholder="New platform e-mail"
                       v-model="platformEmail">

                <label for="platformEmailPassword">
                    E-mail password:
                </label>
                <input type="password" id="platformEmailPassword"
                       class="form-control"
                       placeholder="New platform e-mail password"
                       v-model="platformEmailPassword">

                <label for="platformEmailHost">
                    E-mail host:
                </label>
                <input type="text" id="platformEmailHost"
                       class="form-control"
                       placeholder="New platform e-mail host"
                       v-model="platformEmailHost">

                <label for="platformEmailDriver">
                    E-mail driver:
                </label>
                <input type="text" id="platformEmailDriver"
                       class="form-control"
                       placeholder="New platform e-mail driver"
                       v-model="platformEmailDriver">

                <label for="platformEmailPort">
                    E-mail port:
                </label>
                <input type="text" id="platformEmailPort"
                       class="form-control"
                       placeholder="New platform e-mail host"
                       v-model="platformEmailPort">

                <label for="platformEmailEncryption">
                    E-mail encryption:
                </label>
                <input type="text" id="platformEmailEncryption"
                       class="form-control"
                       placeholder="New platform e-mail encryption"
                       v-model="platformEmailEncryption">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-offset-4 col-md-2 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-info btn-block"
                        @click.prevent="goBack">
                    Cancel
                </button>
            </div>
            <div class="col-md-2 col-xs-6">
                <button type="submit"
                        class="btn btn-lg btn-primary btn-block"
                        @click.prevent="editPConfig">
                    Save
                </button>
            </div>
        </div>
    </form>
</div></template>

<script type="text/javascript">

    export default{
        data: function() {
            return {
                showSuccess: false,
                showFailure: false,
                successMessage: '',
                failMessage: [],
                platformEmail: '',
                platformEmailPassword: '',
                platformEmailHost: '',
                platformEmailDriver: '',
                platformEmailPort: '',
                platformEmailEncryption: '',

            }
        },
        methods: {
            goBack: function(){
                this.$router.push({ path: '/admin' });
            },
            editPConfig: function(){
                this.showSuccess = true;
                this.showFailure = false;
                this.successMessage = 'Updating...';

                this.$root.axiosInstance.put(
                    '../api/platform-email/1',
                    {
                        email: this.platformEmail,
                        platform_email_properties: {
                            driver: this.platformEmailDriver,
                            host: this.platformEmailHost,
                            port: this.platformEmailPort,
                            password: this.platformEmailPassword,
                            encryption: this.platformEmailEncryption
                        }
                    })
                .then(response => {
                    this.showSuccess = true;
                    this.showFailure = false;
                    this.successMessage = response.data;
                }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.$root.pushLaravelValidationErrorsTo(
                        this.failMessage,
                        response,
                        "Unable to update configurations:"
                    );
                });
            },
            getPlatformEmail: function(){
                this.$root.axiosInstance.get('../api/platform-email/1')
                    .then(response => {
                        this.platformEmail = response.data.platform_email;
                        this.platformEmailDriver = response.data.props.driver;
                        this.platformEmailPassword = response.data.props.password;
                        this.platformEmailPort = response.data.props.port;
                        this.platformEmailEncryption = response.data.props.encryption;
                        this.platformEmailHost = response.data.props.host;
                    });
            },
        },
        created() {
            this.getPlatformEmail();
        }
    }
</script>
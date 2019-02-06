<template><div>
    <div class="row">
        <div v-bind:class="[isSuccess ? 'alert-success' : 'alert-danger', 'alert']">
            <strong >{{ message }}</strong>
        </div>
    </div>
</div></template>

<script type="text/javascript">

    export default {
        data: function () {
            return {
                isSuccess: true,
                message: '',
                user: {
                    email: this.$route.params.email,
                    token: this.$route.params.token
                }
            }
        },
        mounted(){
            axios.post('../../api/complete-registration', this.user )
                .then(response => {
                    this.isSuccess = true;
                    this.message = 'Thank you for registering with us! Redirecting you to the login page..'
                    var self = this;
                    setTimeout(function () {
                        self.$router.push({ name: 'login' });
                    }, 3000);
                })
                .catch(response => {
                    this.isSuccess = false;
                    this.message = 'This link is not active anymore. Redirecting you to the login page..';
                    setTimeout(() => {
                        this.$router.push({ name: 'login' });
                    }, 3000);
                });
        },
    }
</script>
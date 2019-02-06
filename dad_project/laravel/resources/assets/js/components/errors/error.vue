<template><div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h1>
                        Oops!</h1>
                    <h2>
                        {{ errorCode }}</h2>
                    <div class="error-details">
                        Sorry, an error has occured, {{ errorMessage }}
                    </div>
                    <div class="error-actions">
                        <router-link to="/"
                                     class="btn btn-primary btn-lg">
                            <span class="glyphicon glyphicon-home"></span>
                            Back to Home
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
</div></template>


<script type="text/javascript">
    export default {
        props: {
            errorCode: {
                Type: Number,
                required: false,
                default: 404,
                validator: function (value) {
                    return value.toString().length == 3
                }
            },
            errorMessage: {
                Type: String,
                required: false,
                default: 'Requested page not found!'
            },
        },
    }
</script>

<style scoped>
    .error-template {padding: 40px 15px;text-align: center;}
    .error-actions {margin-top:15px;margin-bottom:15px;}
    .error-actions .btn { margin-right:10px; }
</style>
<template><div>

</div></template>

<script type="text/javascript">
    export default{
        props: {
            exampleVar: {
                Type: String,
                required: true,
                default: 400,
                validator: function (value) {
                    return value.length = 3
                }
            },
        },
        data: function() {
            return {
                showSuccess: false,
                showFailure: false,
                successMessage: '',
                failMessage: '',
            }
        },
        methods: {
            example: function(){
                this.$root.axiosInstance.get('../api/example')
                .then(response => {
                    console.dir(response);
                }).catch(response => {
                    console.dir(response);
                });
            },

        },
        created() {
            this.$root.whenReadyDo(this.example);
        }
    }
</script>
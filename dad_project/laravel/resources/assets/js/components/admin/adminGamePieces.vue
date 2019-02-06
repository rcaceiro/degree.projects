<template>
    <div>

        <vue-tabs>
            <v-tab title="Tiles Images">
                <div class="row">
                    <div class="alert alert-success" v-if="showSuccess">
                        <button type="button" class="btn btn-link pull-right" v-on:click="showSuccess=false">&times;
                        </button>
                        <strong>{{ successMessage }}</strong>
                    </div>

                    <div class="alert alert-danger" v-if="showFailure">
                        <button type="button" class="btn btn-link pull-right" v-on:click="showFailure=false">&times;
                        </button>
                        <strong>{{ failMessage }}</strong>
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive col-md-8 ">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!--v-if="index%2===0"-->
                            <tr v-for="(image) in imagesTiles"  :key="image.id"
                                :class="image.active===0 ? 'danger' : ''">
                                <td><img v-bind:src="'../img/'+ image.path"></td>
                                <td>
                                    <a v-if="image.active===1" class="btn btn-xs btn-warning"
                                       v-on:click.prevent="blockImage(image)">Deactivate</a>
                                    <a v-if="image.active===0" class="btn btn-xs btn-success"
                                       v-on:click.prevent="unblockImage(image)">Activate</a>
                                    &nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <a class="btn btn-xs btn-danger" v-on:click.prevent="deleteImage(image)">Delete</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--<div class="table-responsive col-md-6">-->
                        <!--<table class="table table-striped table-hover">-->
                            <!--<thead>-->
                            <!--<tr>-->
                                <!--<th>Image</th>-->
                                <!--<th>Actions</th>-->
                            <!--</tr>-->
                            <!--</thead>-->
                            <!--<tbody>-->
                            <!--<tr v-for="(image, index) in imagesTile" v-if="index%2===1" :key="image.id"-->
                                <!--:class="image.active===0 ? 'danger' : ''">-->
                                <!--<td><img v-bind:src="'../img/'+ image.path"></td>-->
                                <!--<td>-->
                                    <!--<a v-if="image.active===1" class="btn btn-xs btn-warning"-->
                                       <!--v-on:click.prevent="blockImage(image)">Deactivate</a>-->
                                    <!--<a v-if="image.active===0" class="btn btn-xs btn-success"-->
                                       <!--v-on:click.prevent="unblockImage(image)">Activate</a>-->
                                <!--</td>-->
                                <!--<td>-->
                                    <!--<a class="btn btn-xs btn-danger" v-on:click.prevent="deleteImage(image)">Delete</a>-->
                                <!--</td>-->
                            <!--</tr>-->
                            <!--</tbody>-->
                        <!--</table>-->
                    <!--</div>-->

                </div>

                <div :class="'ui right floated pagination menu'">

                    <button :class="[pagesTiles.current_page > 1 ? '' : 'disabled', 'btn btn-info']"
                            v-on:click.prevent="previousPageTiles(pagesTiles.current_page-1)">Previous
                    </button>

                    Page {{pagesTiles.current_page}} of {{pagesTiles.last_page}}.

                    <button :class="[pagesTiles.current_page < pagesTiles.last_page ? '' : 'disabled', 'btn btn-info']"
                            v-on:click.prevent="nextPageTiles(pagesTiles.current_page+1)">Next
                    </button>

                </div>
            </v-tab>
            <v-tab title="Hidden Images">
                <div class="row">
                    <div class="alert alert-success" v-if="showSuccess">
                        <button type="button" class="btn btn-link pull-right" v-on:click="showSuccess=false">&times;
                        </button>
                        <strong>{{ successMessage }}</strong>
                    </div>

                    <div class="alert alert-danger" v-if="showFailure">
                        <button type="button" class="btn btn-link pull-right" v-on:click="showFailure=false">&times;
                        </button>
                        <strong>{{ failMessage }}</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="table-responsive col-md-8">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!--v-if="index%2===0"-->
                            <tr v-for="(image) in imagesHidden"  :key="image.id"
                                :class="image.active===0 ? 'danger' : ''">
                                <td><img v-bind:src="'../img/'+ image.path"></td>
                                <td>
                                    <a v-if="image.active===1" class="btn btn-xs btn-warning"
                                       v-on:click.prevent="blockImage(image)">Deactivate</a>
                                    &nbsp;&nbsp;&nbsp;
                                    <a v-if="image.active===0" class="btn btn-xs btn-success"
                                       v-on:click.prevent="unblockImage(image)">Activate</a>
                                    <a class="btn btn-xs btn-danger" v-on:click.prevent="deleteImage(image)">Delete</a>
                                </td>



                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div :class="'ui right floated pagination menu'">

                    <button :class="[pagesHidden.current_page > 1 ? '' : 'disabled', 'btn btn-info']"
                            v-on:click.prevent="previousPageHidden(pagesHidden.current_page-1)">Previous
                    </button>

                    Page {{pagesHidden.current_page}} of {{pagesHidden.last_page}}.

                    <button :class="[pagesHidden.current_page < pagesHidden.last_page ? '' : 'disabled', 'btn btn-info']"
                            v-on:click.prevent="nextPageHidden(pagesHidden.current_page+1)">Next
                    </button>

                </div>
            </v-tab>


            <v-tab title="Upload Images" >
                <div class="table-responsive">

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!files.length">
                            <td colspan="7">
                                <div class="text-center p-5">
                                    <br/>
                                    <h4>Drop files anywhere to upload</h4>
                                    <br/>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(file, index) in files" :key="file.id">
                            <td>{{index}}</td>
                            <td>
                                <div class="filename">
                                    {{file.name}}
                                </div>
                                <div class="progress" v-if="file.active || file.progress !== '0.00'">
                                    <div :class="{'progress-bar': true, 'progress-bar-striped': true, 'bg-danger': file.error, 'progress-bar-animated': file.active}" role="progressbar" :style="{width: file.progress + '%'}">{{file.progress}}%</div>
                                </div>
                            </td>
                            <td>{{file.size}}</td>

                            <td v-if="file.error">{{file.error}}</td>
                            <td v-else-if="file.success">success</td>
                            <td v-else-if="file.active">active</td>
                            <td v-else></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-secondary btn-sm" type="button"
                                            href="#" @click.prevent="$refs.upload.remove(file)">
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group col-md-2">
                    <select v-model="selected" class="form-control">
                        <option v-for="option in options" v-bind:value="option.value">
                            {{ option.text }}
                        </option>
                    </select>
                </div>

                <div class="btn-group">
                    <file-upload
                             class="btn btn-primary"
                             accept="image/png,image/jpeg"
                             extensions="jpg,png"
                             :multiple="true"
                             :drop="true"
                             :drop-directory="false"
                             v-model="files"
                             @input-filter="inputFilter"
                             post-action="../api/images"
                             :headers="{'Accept': 'application/json',
                                        'Authorization': token}"
                             ref="upload">
                        Select images
                    </file-upload>
                </div>

                <button
                        @click.prevent="upload()"
                        type="button"
                        class="btn btn-success"
                >
                    Start upload
                </button>
            </v-tab>
        </vue-tabs>
    </div>
</template>

<script type="text/javascript">
    import FileUpload from 'vue-upload-component/dist/vue-upload-component.part.js'
    import VueNotifications from 'vue-notifications';
    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';

    export default {
        components: {
            FileUpload
        },
        data: function () {

            return {
                token: 'Bearer ' + sessionStorage.getItem('access_token'),
                files:[],
                title: "Edit Images",
                showSuccess: false,
                showFailure: false,
                successMessage: '',
                failMessage: '',
                imagesTiles: [],
                imagesHidden: [],
                selected: 'tile',
                options: [
                    { text: 'Tile', value: 'tile' },
                    { text: 'Hidden', value: 'hidden' }

                ],
                pagesTiles:[],
                linksTiles:[],
                pagesHidden:[],
                pagesHidden:[],
            }
        },
        computed: {
            /*imagesHidden: function () {

                return this.images.filter(images => images.face === 'hidden');
            },
            imagesTile: function () {
                this.rows=this.images.filter(images => images.face === 'tile');
                return this.images.filter(images => images.face === 'tile')
            },*/
        },
        methods: {
            imagePath: function (image) {
                return image.path;
            },
            blockImage: function (image) {

                this.image = image;

                this.$root.axiosInstance.patch('../api/images/' + image.id, {active: 0})
                    .then(response => {
                        this.showSuccess = true;
                        this.successMessage = "Deactivated!";
                        this.showFailure = false;
                        Object.assign(this.image, response.data.data);

                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    let data = response.response.data;
                    if(data == null){
                        this.failMessage = 'Error'
                    }
                    this.failMessage = data;
                });
            },
            unblockImage: function (image) {

                this.image = image;

                this.$root.axiosInstance.patch('../api/images/' + image.id, {active: 1})
                    .then(response => {
                        this.showSuccess = true;
                        this.successMessage = "Activated!";
                        this.showFailure = false;
                        Object.assign(this.image, response.data.data);

                    }).catch(response => {

                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'
                });
            },
            deleteImage: function (image) {
                this.$root.axiosInstance.delete('../api/images/' + image.id)
                    .then(response => {
                        this.showSuccess = true;
                        this.successMessage = 'Image Deleted';
                        this.showFailure = false;
                        this.loadImg();
                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    let data = response.response.data;
                    if(data == null){
                        this.failMessage = 'Error'
                    }
                    this.failMessage = data;

                });
            },
            /*getImages: function () {
                this.$root.axiosInstance.get('../api/images')
                    .then(response => {
                        this.images = response.data.data;

                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'

                });
            },*/
            getImagesTiles: function (num) {
                this.$root.axiosInstance.get('../api/imagestile?page='+num)
                    .then(response => {
                        this.imagesTiles = response.data.data;
                        this.pagesTiles = response.data.meta;
                        this.linksTiles = response.data.links;



                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'

                });
            },
            getImagesHidden: function (num) {
                this.$root.axiosInstance.get('../api/imageshidden?page='+num )
                    .then(response => {
                        this.imagesHidden = response.data.data;
                        this.pagesHidden = response.data.meta;
                        this.linksHidden = response.data.links;

                    }).catch(response => {
                    this.showSuccess = false;
                    this.showFailure = true;
                    this.failMessage = 'Error'

                });
            },
            upload(){

                //img type tile or hidden
                let images = [];

                for (let i = 0; i < this.files.length; i++) {
                    images.push(this.files[i].fileBase64);
                }
                this.$root.axiosInstance.post('../api/images', {
                    images: images,
                    type : this.selected
                })
                .then(response => {
                    this.$refs.upload.clear();
                    this.loadImg();
                    this.showSuccessMsg({
                        title: 'Uploaded successfully!',
                        message: ''
                    });

                }).catch(response => {
                    this.showErrorMsg({
                        title: 'Ops! Something failed',
                        message: ''
                    });
                });
            },
            inputFilter: function (newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    // Before adding a file
                    // Filter system files or hide files
                    if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
                        return prevent()
                    }
                    // Filter php html js file
                    if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
                        return prevent()
                    }
                    // Filter non-image file
                    if (!/\.(jpeg|jpg|png)$/i.test(newFile.name)) {
                        return prevent()
                    }

                    var reader = new FileReader();
                    reader.readAsDataURL(newFile.file);
                    reader.onload = () => {
                        let fileBase64 = reader.result;
                        this.$refs.upload.update(newFile,
                            {   error: '',
                                fileBase64
                            });

                    };
                    reader.onerror = () => {
                        return prevent()
                    };
                }

            },
            nextPageTiles: function (num) {
                if (num - 1 < this.pagesTiles.last_page) {
                    this.getImagesTiles(num);
                }

            },
            previousPageTiles: function (num) {
                if (num >= 1) {
                    this.getImagesTiles(num);
                }

            },
            nextPageHidden: function (num) {
                if (num - 1 < this.pagesHidden.last_page) {
                    this.getImagesHidden(num);
                }

            },
            previousPageHidden: function (num) {
                if (num >= 1) {
                    this.getImagesHidden(num);
                }

            },
            loadImg: function(){
                //this.getImages();
                this.getImagesHidden(1);
                this.getImagesTiles(1);
            },
        },
        notifications: {
            showSuccessMsg: {
                type: VueNotifications.types.success,
                title: 'default',
                message: 'default'
            },
            showErrorMsg: {
                type: VueNotifications.types.error,
                title: 'default',
                message: 'default'
            }
        },
        created() {
            //this.getImages();
            this.loadImg();
        }
    }
</script>

<style>

    .btn-xs {
        width: 6em;
        padding: 2% 0 2% 0;
    }


    .file-uploads {
        overflow: hidden;
        position: relative;
        text-align: center;
        display: inline-block;
    }
    .file-uploads.file-uploads-html4 input[type="file"] {
        opacity: 0;
        font-size: 20em;
        z-index: 1;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        position: absolute;
        width: 100%;
        height: 100%;
    }
    .file-uploads.file-uploads-html5 input[type="file"] {
        overflow: hidden;
        position: fixed;
        width: 1px;
        height: 1px;
        z-index: -1;
        opacity: 0;
    }

    th{
        text-align: center;
    }
    td{
        text-align: center;
    }
</style>
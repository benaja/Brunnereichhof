<template>
  <div class="file-upload">
    <div class="form-group">
      <label for="logo" class="control-label">Attachments</label>
      <br />
      <br />
      <div class="form-group">
        <input type="file" multiple="multiple" id="attachments" @change="uploadFieldChange" />
        <hr />
        <div class="form-group files">
          <div
            class="attachment-holder animated fadeIn"
            v-cloak
            v-for="(attachment, index) in attachments"
            :key="index"
          >
            <div class="form-group">
              <span
                class="label label-primary"
              >{{ attachment.name + ' (' + Number((attachment.size / 1024 / 1024).toFixed(1)) + 'MB)'}}</span>
              <span style="background: red; cursor: pointer;" @click="removeAttachment(attachment)">
                <button class="btn btn-xs btn-danger">Remove</button>
              </span>
            </div>
          </div>
        </div>
      </div>
      <button class="btn btn-primary" @click="submit">Upload</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FileUpload',
  props: ['settings'],
  data() {
    return {
      attachments: [],
      attachment_labels: [],
      data: new FormData(),
      percentCompleted: 0
    }
  },
  watch: {},
  computed: {},
  methods: {
    validate() {
      if (!this.attachments.length) {
        console.log('Add files')
        return false
      }
      return true
    },
    getAttachmentSize() {
      this.upload_size = 0
      this.attachments.map(item => {
        this.upload_size += parseInt(item.size)
      })

      this.upload_size = Number(this.upload_size.toFixed(1))
      this.$forceUpdate()
    },
    prepareFields() {
      for (let i = this.attachments.length - 1; i >= 0; i--) {
        console.log(this.attachments[i].category_id)
        this.data.append('attachments[][0]', this.attachments[i])
        this.data.append('attachments[][1]', this.attachments[i].category_id)
      }
      for (let i = this.attachment_labels.length - 1; i >= 0; i--) {
        this.data.append('attachment_labels[]', JSON.stringify(this.attachment_labels[i]))
      }
    },
    removeAttachment(attachment) {
      this.attachments.splice(this.attachments.indexOf(attachment), 1)

      this.getAttachmentSize()
    },
    uploadFieldChange(e) {
      let files = e.target.files || e.dataTransfer.files
      if (!files.length) {
        return
      }
      for (let i = files.length - 1; i >= 0; i--) {
        this.attachments.push(files[i])
      }
      document.getElementById('attachments').value = []
    },
    submit() {
      this.prepareFields()
      if (!this.validate()) {
        return false
      }
      let config = {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: function(progressEvent) {
          this.percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
          console.log(this.percentCompleted)
          this.$forceUpdate()
        }.bind(this)
      }
      this.axios
        .post(`/rooms/${this.$route.params.id}`, this.data, config)
        .then(
          function(response) {
            console.log(response)
            if (response.data.success) {
              console.log('Successfull upload')
              this.resetData()
            } else {
              console.log('Unsuccessful Upload')
            }
          }.bind(this)
        )
        .catch(function(error) {
          console.log(error)
        })
    },
    resetData() {
      this.data = new FormData()
      this.attachments = []
    },
    start() {
      console.log('Starting File Management Component')
      this.pullCategories()
    }
  },
  created() {
    this.start()
  }
}
</script>

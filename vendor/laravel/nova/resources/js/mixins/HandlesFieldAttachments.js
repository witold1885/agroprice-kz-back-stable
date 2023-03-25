import isNil from 'lodash/isNil'
import { mapProps } from './propTypes'

export default {
  props: mapProps(['resourceName']),

  async created() {
    if (this.field.withFiles) {
      const {
        data: { draftId },
      } = await Nova.request().get(
        `/nova-api/${this.resourceName}/field-attachment/${this.fieldAttribute}/draftId`
      )

      this.draftId = draftId
    }
  },

  data: () => ({ draftId: null }),

  methods: {
    /**
     * Upload an attachment
     */
    uploadAttachment(file, { onUploadProgress, onCompleted, onFailure }) {
      const data = new FormData()
      data.append('Content-Type', file.type)
      data.append('attachment', file)
      data.append('draftId', this.draftId)

      if (isNil(onUploadProgress)) {
        onUploadProgress = () => {}
      }

      if (isNil(onFailure)) {
        onFailure = () => {}
      }

      if (isNil(onCompleted)) {
        throw 'Missing onCompleted parameter'
      }

      Nova.request()
        .post(
          `/nova-api/${this.resourceName}/field-attachment/${this.fieldAttribute}`,
          data,
          { onUploadProgress }
        )
        .then(({ data: { url } }) => {
          return onCompleted(url)
        })
        .catch(error => {
          onFailure(error)

          Nova.error(this.__('An error occurred while uploading the file.'))
        })
    },

    /**
     * Remove an attachment from the server
     */
    removeAttachment(attachmentUrl) {
      Nova.request()
        .delete(
          `/nova-api/${this.resourceName}/field-attachment/${this.fieldAttribute}`,
          { params: { attachmentUrl } }
        )
        .then(response => {})
        .catch(error => {})
    },

    /**
     * Purge pending attachments for the draft
     */
    clearAttachments() {
      if (this.field.withFiles) {
        Nova.request()
          .delete(
            `/nova-api/${this.resourceName}/field-attachment/${this.fieldAttribute}/${this.draftId}`
          )
          .then(response => {})
          .catch(error => {})
      }
    },

    /**
     * Fill draft id for the field
     */
    fillAttachmentDraftId(formData) {
      this.fillIfVisible(
        formData,
        `${this.fieldAttribute}DraftId`,
        this.draftId
      )
    },
  },
}

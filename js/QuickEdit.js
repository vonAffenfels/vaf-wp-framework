export class QuickEdit {
    static fromPostIdFieldName(postId, fieldName) {
    	const quickEdit = new this;

        quickEdit.postId = postId;
        quickEdit.fieldName = fieldName;

    	return quickEdit;
    }

    get initialValue() {
        JSON.parse(
            document.querySelector(`#post-${postId} .{fieldName}`).innerText()
        );
    }
}

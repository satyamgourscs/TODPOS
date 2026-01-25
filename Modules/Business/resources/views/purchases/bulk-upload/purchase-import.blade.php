<div class="modal fade" id="bulk-upload-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bulk Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="erp-table-section">
                    <div class="container-fluid">
                        <div class="border-0">
                            <div class="card-bodys">
                                <form action="{{ route('business.purchases.bulk-store') }}" method="post" enctype="multipart/form-data" class="ajaxform_instant_reload">
                                    @csrf
                                    <div class="bulk-upload-container w-100">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <div class="bulk-input w-100">
                                                <input class="form-control w-100" type="file" name="file" required>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                            <button type="submit" class="add-order-btn process-csv-btn rounded-2 border-0 submit-btn mt-3">Submit</button>
                                            <a href="{{ asset('assets/PosPro_purchase_bulk_upload.xlsx') }}" download="PosPro_purchase_bulk_upload.xlsx" class="download-file-btn mt-3">Download Sample File</a>
                                        </div>
                                    </div>
                                    <div class="bulk-upload-container mt-3">
                                        <div class="instruction-header">
                                            <h5>Instructions</h5>
                                            <div class="mt-3">
                                                <h6><strong>Note: </strong> Please follow the instructions below to upload your file.</h6>
                                                <ul>
                                                    <li><b>1.</b> Download the sample file first and add all your purchases data to it.</li>
                                                    <li><b>2.</b> <span class="text-danger">*</span> Indicates a required field. If you do not provide the required fields, the system will ignore except product information.</li>
                                                    <li><b>3.</b> After adding all your purchases, please save the file and then upload the updated version.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

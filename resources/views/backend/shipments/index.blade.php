@php
    use Illuminate\Support\Facades\Auth;$role = Auth::user()->role;
@endphp
@extends('backend.layouts.app')
@section('PageTitle', __('Shipments'))
@section('content')
    <!--breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{__('Shipments')}}</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{route($role . '-profile')}}"><i class="bx
                    bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{__('Shipments')}} List</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb -->

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
{{--                <div class="ms-auto" style="margin-bottom: 20px">--}}
{{--                    <a href="add_brand" class="btn btn-primary radius-30 mt-2 mt-lg-0">--}}
{{--                        <i class="bx bxs-plus-square"></i>Add New Brand</a>--}}
{{--                </div>--}}

                <table id="data_table" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th> Serial Number</th>
                        <th>View Details</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{$item->serial_num}}</td>

                            <td>
                                <button type="button" class="btn btn-primary btn-sm radius-30 px-4"
                                        data-bs-toggle="modal"
                                        data-bs-target="#exampleVerticallycenteredModal-{{$item->id}}">View
                                    Details
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleVerticallycenteredModal-{{$item->id}}"
                                     tabindex="-1"
                                     aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Shipment Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
{{--                                                <img src="{{url('uploads/images/brand/' . $item->brand_image)}}"--}}
{{--                                                     class="card-img-top" style="max-width: 300px; margin-left:--}}
{{--                                                         10px">--}}
                                                <div class="card-body">
                                                    <h5 class="card-title">Brand Name : <span style="font-weight:
                                                         lighter">{{''}}</span></h5>
                                                    <h5 class="card-title">Brand Slug : <span style="font-weight:
                                                         lighter">{{''}}</span></h5>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex order-actions">
                                    <a href="" class="edit_shipment" data-id="{{$item->id}}" data-url="{{url('shipment/'.$item->id.'/edit')}}" data-update_url="{{url('shipment/'.$item->id)}}" data-bs-toggle="modal" data-bs-target="#exampleFullScreenModal">
                                        <i class='bx bxs-edit'></i>
                                    </a>

                                    <a  class="ms-3 delete" data-bs-toggle="modal" data-url="{{route('shipment.destroy', $item->id) }}" data-bs-target="#deleteModal">
                                        <i class='bx bxs-trash'></i>
                                    </a>

                                </div>
                            </td>
                        </tr>

                    @endforeach
                </table>
            </div>
        </div>
    </div>

<!-- edit modal -->
<div class="modal fade" id="exampleFullScreenModal"
     tabindex="-1"
     aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> {{__('Edit Shipment')}}</h5>
                <button type="button" class="btn-close"  data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <form class="brand_form" action="" method="POST" enctype="multipart/form-data">
                            @csrf
                           {{ method_field('PUT') }}

                            <input name="brand_id" value="{{$item->id}}"
                                   hidden/>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Serial Number</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="serial_num" type="text" class="form-control serial_num" readonly required autofocus/>
                                    <small style="color: #e20000" class="error"
                                           id="serial_num-error"></small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Status</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select class="form-control status" id="status" name="status">
                                        <!-- Options will be populated dynamically -->
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit"  class="btn btn-primary px-4"   value="Save Changes" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>
    <!-- delete modal-->
    <div class="modal fade" id="deleteModal"
         tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Sure ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" class="delete_form">
                    @csrf
                    @method('DELETE')
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('plugins')
    <link href="{{asset('backend_assets')}}/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
@endsection
@section('js')
    <script src="{{asset('backend_assets')}}/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="{{asset('backend_assets')}}/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#data_table').DataTable({
                lengthChange: true,
                buttons: ['excel', 'pdf', 'print']
            });

            table.buttons().container()
                .appendTo('#data_table_wrapper .col-md-6:eq(0)');
        });
    </script>

    <script src="sweetalert2.all.min.js"></script>



    @section('AjaxScript')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function () {
                $('.edit_shipment').on('click', function (event) {
                    event.preventDefault();

                    // Get the shipment ID from the button's data-id attribute
                    let shipmentId = $(this).data('id');
                    let url = $(this).data('url');
                    let updateUrl = $(this).data('update_url');
                    $.ajax({
                        url: url,
                        method: 'get',
                        data: {
                            id: shipmentId, // Send shipment ID
                            _token: "{{ csrf_token() }}" // Include CSRF token for security
                        },
                        dataType: 'JSON',
                        success: function (response) {
                            $('.serial_num').val(response.serial_num);

                            // Populate status dropdown
                            let statusOptions = '';
                            const statusValues = {
                                '1': 'Pending',
                                '2': 'On The Way',
                                '3': 'Paid',
                                '4': 'Delivered',
                                '5': 'Cancelled'
                            };

                            $.each(statusValues, function (key, value) {
                                let selected = response.status == key ? 'selected' : ''; // Pre-select current status
                                statusOptions += `<option value="${key}" ${selected}>${value}</option>`;
                            });

                            $('.status').html(statusOptions);

                            // Dynamically set the form's action attribute
                            $('.brand_form').attr('action', updateUrl);
                        },
                        error: function (response) {
                            var res = $.parseJSON(response.responseText);
                            $.each(res.errors, function (key, err) {
                                $('#' + key + '-error').text(err[0]);
                                $('#' + key).addClass('is-invalid');
                            });
                        }
                    });
                });
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.delete').on('click', function (event) {
                    event.preventDefault();
                    let url = $(this).data('url');
                    $('.delete_form').attr('action', url);
                });
            });
        </script>
    @endsection

@endsection

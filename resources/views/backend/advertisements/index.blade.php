@php
    use Illuminate\Support\Facades\Auth;$role = Auth::user()->role;
@endphp
@extends('backend.layouts.app')
@section('PageTitle', __('Advertisements'))
@section('content')
    <!--breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{__('Advertisements')}}</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{route($role . '-profile')}}"><i class="bx
                    bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{__('Advertisements list')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb -->

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <div class="ms-auto" style="margin-bottom: 20px">
                    <a href="{{route('countries.create')}}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
                        <i class="bx bxs-plus-square"></i>{{__('Add new Advertisement')}}</a></div>

                <table id="data_table" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                        <tr id="tr{{$item->id}}" data-id="{{$item->id}}">
                            <td>{{$item->name}}</td>
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
                                                <h5 class="modal-title">Countries Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card-body">
                                                    <h5 class="card-title"> Name : <span style="font-weight:
                                                         lighter">{{$item->name}}</span></h5>
                                                    <div class="col-lg-5 col-md-5">
                                                        <h5 class="card-title">Restrictions :</h5>

                                                    </div>
                                                    <div class="col-lg-5 col-md-5 mt-2">

                                                    </div>
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
                                    <a href="{{url('advertisements/'.$item->id.'/edit')}}" class=""  ><i class='bx bxs-edit'></i></a>

                                    <a  class="ms-3 delete" data-bs-toggle="modal" data-url="{{route('advertisements.destroy', $item->id) }}" data-bs-target="#deleteModal">
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
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
                integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.brand_form').on('submit', function (event) {
                    event.preventDefault();
                    // remove errors if the conditions are true
                    $('.brand_form *').filter(':input.is-invalid').each(function () {
                        this.classList.remove('is-invalid');
                    });
                    $('.brand_form *').filter('.error').each(function () {
                        this.innerHTML = '';
                    });
                    $.ajax({
                        url: "{{route('brand-update')}}",
                        method: 'POST',
                        data: new FormData(this),
                        dataType: 'JSON',
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function (response) {
                            // remove errors if the conditions are true
                            $('.brand_form *').filter(':input.is-invalid').each(function () {
                                this.classList.remove('is-invalid');
                            });
                            $('.brand_form *').filter('.error').each(function () {
                                this.innerHTML = '';
                            });
                            Swal.fire({
                                icon: 'success',
                                title: response.msg,
                                showDenyButton: false,
                                showCancelButton: false,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                window.location.reload();
                            });
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

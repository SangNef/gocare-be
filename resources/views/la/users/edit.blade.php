@extends("la.layouts.app")

@section("contentheader_title")
    <a href="{{ url(config('laraadmin.adminRoute') . '/users') }}">Users</a> :
@endsection
@section("contentheader_description", $user->$view_col)
@section("section", "Users")
@section("section_url", url(config('laraadmin.adminRoute') . '/users'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Users Edit : ".$user->$view_col)

@section("main-content")

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box">
        <div class="box-header">

        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    {!! Form::model($user, ['route' => [config('laraadmin.adminRoute') . '.users.update', $user->id ], 'method'=>'PUT', 'id' => 'user-edit-form']) !!}
                    @la_input($module, 'name')
                    @la_input($module, 'email')
                    <div @if(!Entrust::hasRole("SUPER_ADMIN")) style="display: none" @endif>
                        <div class="form-group">
                            <label for="role">Phân quyền*:</label>
                            <select class="form-control" required="1" data-placeholder="Chọn nhóm" name="role">
                                <?php $roles = \App\Role::availableRoles() ?>
                                @foreach($roles as $role)
                                    @if($user->hasRole($role->name))
                                        <option value="{{ $role->id }}" selected>{{ $role->display_name }}</option>
                                    @else
                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="form-group">
                        {!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!}
                        <button class="btn btn-default pull-right">
                            <a href="{{ url(config('laraadmin.adminRoute') . '/users') }}">Huỷ</a>
                        </button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $("#user-edit-form").validate({});
        });

    </script>
@endpush

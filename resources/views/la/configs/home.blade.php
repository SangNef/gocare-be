@extends("la.layouts.app")

@section("contentheader_title", 'Cài đặt trang home')
@section("contentheader_description", "")
@section("section", "Home Page")
@section("sub_section", "")
@section("htmlheader_title", "Configuration")

@section("headerElems")
@endsection

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
    <form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
        <!-- general form elements disabled -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Banner / Popup</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                {{ csrf_field() }}
                <div class="form-group">
                    <input class="form-check-input" type="checkbox" name="home_section_1[popup]" id="flexCheckDefault"
                           value="1" {{(isset($home_section1->popup) && $home_section1->popup=="1")?'checked':''}}>
                    <label for="flexCheckDefault">Bật Popup</label>
                </div>
                <div class="form-group">
                    <label>Link Popup</label>
                    <input type="text" class="form-control" name="home_section_1[popup_link]"
                           value="{{$home_section1->popup_link??''}}">
                </div>

                <div class="form-group"><label for="image2" style="display:block;">Ảnh Pupop:</label><input
                            class="form-control" placeholder="Enter Ảnh" name="home_section_1_popup_img"
                            type="hidden"
                            value="{{$home_section1->popup_img??''}}"><a
                            class="btn btn-default btn_upload_image hide"
                            file_type="image"
                            selecter="home_section_1_popup_img">Upload <i
                                class="fa fa-cloud-upload"></i></a>
                    <div class="uploaded_image"><img
                                src="{{$home_section1->imagepopup??''}}"><i
                                title="Remove Image" class="fa fa-times"></i></div>
                </div>
                <h3 class="box-title">Banner</h3>
                <hr/>
                <div class="form-group">
                    <label>Tiêu đề</label>
                    <input type="text" class="form-control" name="home_section_1[title]"
                           value="{{$home_section1->title??''}}">
                </div>
                <div class="form-group">
                    <label>mô tả</label>
                    <textarea name="home_section_1[subtitle]"
                              style="width: 100%;height: 80px;">{{$home_section1->subtitle??''}}</textarea>
                </div>
                <div class="form-group">
                    <label>Link</label>
                    <input type="text" class="form-control" name="home_section_1[link]"
                           value="{{$home_section1->link??''}}">
                </div>
                <div class="row">
                    <div class="col-4 col-sm-4">
                        <div class="form-group"><label for="image1" style="display:block;">Ảnh 1:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section_1_img1"
                                    type="hidden"
                                    value="{{$home_section1->img1??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section_1_img1">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section1->image1??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-4">
                        <div class="form-group"><label for="image2" style="display:block;">Ảnh 2:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section_1_img2"
                                    type="hidden"
                                    value="{{$home_section1->img2??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section_1_img2">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section1->image2??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-4">
                        <div class="form-group"><label for="image3" style="display:block;">Ảnh 3:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section_1_img3"
                                    type="hidden"
                                    value="{{$home_section1->img3??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section_1_img3">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section1->image3??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div><!-- /.box-footer -->
        </div><!-- /.box -->
    </form>
    <form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
        <!-- general form elements disabled -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Mô hình dịch vụ</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Mô hình PC:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="desktop" type="hidden"
                                    value="{{$home_section2->desktop??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="desktop">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section2->image1??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Mô hình Mobile:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="mobile" type="hidden"
                                    value="{{$home_section2->mobile??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="mobile">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section2->image2??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div><!-- /.box-footer -->
        </div><!-- /.box -->
    </form>
    <form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
        <!-- general form elements disabled -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Chức năng</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text1]"
                               value="{{$home_section3->text1??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img1" type="hidden"
                                    value="{{$home_section3->img1??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img1">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image1??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text2]"
                               value="{{$home_section3->text2??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img2" type="hidden"
                                    value="{{$home_section3->img2??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img2">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image2??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text3]"
                               value="{{$home_section3->text3??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img3" type="hidden"
                                    value="{{$home_section3->img3??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img3">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image3??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text4]"
                               value="{{$home_section3->text4??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img4" type="hidden"
                                    value="{{$home_section3->img4??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img4">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image4??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text5]"
                               value="{{$home_section3->text5??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img5" type="hidden"
                                    value="{{$home_section3->img5??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img5">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image5??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text6]"
                               value="{{$home_section3->text6??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img6" type="hidden"
                                    value="{{$home_section3->img6??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img6">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image6??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text7]"
                               value="{{$home_section3->text7??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img7" type="hidden"
                                    value="{{$home_section3->img7??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img7">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image7??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6">
                        <label>Chức năng</label>
                        <input type="text" class="form-control" name="home_section_3[text8]"
                               value="{{$home_section3->text8??''}}">
                    </div>
                    <div class="col-6 col-sm-6">
                        <div class="form-group"><label for="image1" style="display:block;">Hình ảnh:</label><input
                                    class="form-control" placeholder="Enter Ảnh" name="home_section3_img8" type="hidden"
                                    value="{{$home_section3->img8??''}}"><a
                                    class="btn btn-default btn_upload_image hide"
                                    file_type="image"
                                    selecter="home_section3_img8">Upload <i
                                        class="fa fa-cloud-upload"></i></a>
                            <div class="uploaded_image"><img
                                        src="{{$home_section3->image8??''}}"><i
                                        title="Remove Image" class="fa fa-times"></i></div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div><!-- /.box-footer -->
        </div><!-- /.box -->
    </form>
    <form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
        <!-- general form elements disabled -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Video</h3>
                <button class="btn btn-sm btn-link" type="button" id="add_new_video">Thêm mới</button>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                {{ csrf_field() }}
                <input type="hidden" name="home_section_5[link][]">
                <div class="row" id="add-video">
                    @if(isset($home_section5->link) && count($home_section5->link)>=1)
                        @foreach($home_section5->link as $k=>$v)
                            @if($k>0)
                                <div class="col-sm-12 cus_videos"
                                     style="padding:10px 5px;border-bottom: 1px solid #ebebeb;display: flex;align-items: center;">
                                    <div class="col-sm-6">
                                        <input type="text" name="home_section_5[link][]" class="form-control"
                                               value="{{$v}}"
                                               placeholder="Link video">
                                    </div>
                                    <div class="col-sm-1">
                                        <button type="button" class="btn btn-sm btn-danger remove-videos"><i
                                                    class="fa fa-remove"></i></button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div><!-- /.box-footer -->
        </div><!-- /.box -->
    </form>
    <form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
        <!-- general form elements disabled -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Đánh giá khách hàng</h3>
                <button class="btn btn-sm btn-link" type="button" id="add_cus_item">Thêm mới</button>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                {{ csrf_field() }}
                <input type="hidden" name="home_section_4[name][]">
                <input type="hidden" name="home_section_4[job][]">
                <input type="hidden" name="home_section_4[text][]">
                <div class="row" id="cus-items">
                    @if(isset($home_section4->name) && count($home_section4->name)>=1)
                        @foreach($home_section4->name as $k=>$v)
                            @if($k>0)
                                <div class="col-sm-12 cus_items"
                                     style="padding:10px 5px;border-bottom: 1px solid #ebebeb;display: flex;align-items: center;">
                                    <div class="col-sm-3">
                                        <input type="text" name="home_section_4[name][]" class="form-control"
                                               value="{{$v}}"
                                               placeholder="Họ tên">
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" name="home_section_4[job][]" class="form-control"
                                               value="{{$home_section4->job[$k]??''}}"
                                               placeholder="Nghề nghiệp">
                                    </div>
                                    <div class="col-sm-5">
                                    <textarea name="home_section_4[text][]" class="form-control"
                                              placeholder="Nội dung">{{$home_section4->text[$k]??''}}</textarea>
                                    </div>
                                    <div class="col-sm-1">
                                        <button type="button" class="btn btn-sm btn-danger remove-items"><i
                                                    class="fa fa-remove"></i></button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div><!-- /.box-footer -->
        </div><!-- /.box -->
    </form>
    <form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Ảnh Slider đối tác</h3>
            </div>
            <div class="box-body">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-10">
                        <div class="form-group">
                            <label for="azpro_slider" style="display:block;">
                                Chọn ảnh :
                            </label>
                            <input class="form-control" placeholder="Enter Thư viện ảnh sản phẩm" name="azpro_slider"
                                   type="hidden" value="{{ json_encode(array_keys($sliders)) }}">
                            <div class="uploaded_files">

                                @foreach($sliders as $id => $path)
                                    <a class="uploaded_file2" upload_id="{{ $id }}" target="_blank" href="{{ $path }}">
                                        <img src="{{ $path }}">
                                        <i title="Remove File" class="fa fa-times"></i>
                                    </a>
                                @endforeach
                            </div>
                            <a class="btn btn-default btn_upload_files" file_type="files" selecter="azpro_slider"
                               style="margin-top:5px;">Upload <i class="fa fa-cloud-upload"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')

    <script>
        $(function () {
            $('#add_cus_item').click(function () {
                $('#cus-items').append('' +
                    '<div class="col-sm-12 cus_items" style="padding:10px 5px;border-bottom: 1px solid #ebebeb;display: flex;align-items: center;">\n' +
                    '<div class="col-sm-3">\n' +
                    '<input type="text" name="home_section_4[name][]" class="form-control" value="" placeholder="Họ tên">\n' +
                    '</div>\n' +
                    '<div class="col-sm-3">\n' +
                    '<input type="text" name="home_section_4[job][]" class="form-control" value="" placeholder="Nghề nghiệp">\n' +
                    '</div>\n' +
                    '<div class="col-sm-5">\n' +
                    '<textarea name="home_section_4[text][]" class="form-control" placeholder="Nội dung"></textarea>\n' +
                    '</div>\n' +
                    '<div class="col-sm-1">\n' +
                    '<button type="button" class="btn btn-sm btn-danger remove-items"><i class="fa fa-remove"></i></button>\n' +
                    '</div>' +
                    '</div>')
            });
            $(document).on('click', '.remove-items', function () {
                $(this).parents('.cus_items').remove();
            });
        });
    </script>
    <script>
        $(function() {
            $('#add_new_video').click(function () {
                $('#add-video').append('' +
                '<div class="coll-sm-12 cus_videos" style="padding: 10px 5px;border-bottom: 1px solid #ebebeb;display: flex;align-items: center;">\n' + 
                '<div class="col-sm-6">\n' + 
                '<input type="text" name="home_section_5[link][]" class="form-control" value="" placeholder="Link video youtube">' + 
                '</div>' +    
                '<div class="col-sm-1">\n' +
                '<button type="button" class="btn btn-sm btn-danger remove-items"><i class="fa fa-remove"></i></button>\n' +
                '</div>' +
                '</div>')
            })
            $(document).on('click', '.remove-videos', function() {
                $(this).parents('.cus_videos').remove();
            })
        })
    </script>
@endpush

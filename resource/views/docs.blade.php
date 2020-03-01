<!DOCTYPE html>
<html lang="pt-Br">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width" />
    <title></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        .spacing {
            height: 40px;
        }
        .card-header {
            padding: 0;
        }
        .black {
            background-color: gainsboro;
            padding: 10px 10px 10px 20px;
        }
        .pre {
            white-space: pre;
        }
        .card-title {
            font-size: 16px;
            font-weight: bold;
        }
        .card-subtitle {
            font-size: 16px;
            padding: 20px 0 10px 0;
        }
        .group-title {
            font-size: 16px;
            padding: 10px 0 10px 0;
        }
        .bordered {
            border-bottom: 1px solid rgba(0,0,0,.125) !important;
        }
        .btn.btn-link {
            text-decoration: none;
        }
        .example {
            color: rgba(232, 62, 140, 0.5);
        }
    </style>
</head>
<body>

<div class="container">

    <div class="spacing"></div>

    <div class="accordion" id="docsAccordion">

        @foreach ($data as $item)

            @if($loop->index == 0 || $item->group != $data[$loop->index - 1]->group)
                <h3 class="group-title">{{$item->group}}</h3>
            @endif

            <div class="card {{(isset($data[$loop->index + 1]) && $item->group != $data[$loop->index + 1]->group) ? 'bordered' : null}}">
                <div class="card-header">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{$loop->index}}">
                            <span class="badge badge-secondary">{{$item->method}}</span>
                            <code>{{$item->uri}}</code>
                        </button>
                    </h2>
                </div>

                <div id="collapse{{$loop->index}}" class="collapse" data-parent="#docsAccordion">
                    <div class="card-body">

                        <h4 class="card-title">{{$item->docs->description}}</h4>

                        <h5 class="card-subtitle">Headers</h5>

                        @if(!empty($item->headers))
                            <div class="black"><code><strong>{{$item->headers->name}}: </strong>{{$item->headers->value}}</code></div>
                        @else
                            <div class="black"><code>null</code></div>
                        @endif

                        <h5 class="card-subtitle">Parameters</h5>

                        @if (!empty($item->docs->params))
                            @foreach($item->docs->params as $param)
                                <div class="black"><code><strong>{{$param->name}}: </strong>{{$param->type}}</code></div>
                            @endforeach
                        @else
                            <div class="black"><code>null</code></div>
                        @endif

                        <h5 class="card-subtitle">Request</h5>

                        @if (!empty($item->docs->request))
                            <div class="black">
                                <code>
                                    {
                                    @foreach ($item->docs->request as $req)
                                        <div class="pre">&emsp;<strong>{{$req->name}}</strong>: {{$req->type}}<span class="example">{{!empty($req->example) ? ' (e.g.: '.$req->example.')' : null}}</span>,</div>
                                    @endforeach
                                    }
                                </code>
                            </div>
                        @else
                            <div class="black"><code>{}</code></div>
                        @endif

                        <h5 class="card-subtitle">Response</h5>

                        @if (!empty($item->docs->response) && is_array($item->docs->response))
                            <div class="black">
                                <code>
                                    {
                                    @foreach ($item->docs->response as $res)
                                        <div class="pre">&emsp;<strong>{{$res->name}}</strong>: {{$res->type}},</div>
                                        @if (property_exists($res, 'attributes'))
                                            &emsp;{
                                            @foreach ($res->attributes as $attr)
                                                <div class="pre">&emsp;&emsp;<strong>{{$attr->name}}</strong>: {{$attr->type}}<span class="example">{{!empty($attr->example) ? ' (e.g.: '.$attr->example.')' : null}}</span>,</div>
                                            @endforeach
                                            &emsp;}<br>
                                        @endif
                                    @endforeach
                                    }
                                </code>
                            </div>
                        @elseif (!empty($item->docs->response))
                            <div class="black"><code>{{$item->docs->response}}</code></div>
                        @else
                            <div class="black"><code>{}</code></div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="spacing"></div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>

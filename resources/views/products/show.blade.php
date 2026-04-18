<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$product->name}}</title>
</head>
<body>
    <a href="{{url('/')}}">Назад к каталогу</a>
    <h1>{{$product->name}}</h1>
    <p><strong>Цена:</strong> {{$product->price}}</p>
    <p><strong>Категория:</strong> {{$product->category->name}}</p>
    <p><strong>Описание:</strong></p>
    <p>{{$product->description}}</p>
</body>
</html>

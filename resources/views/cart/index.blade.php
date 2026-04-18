<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Корзина</title>
</head>
<body>
    <a href="{{url('/')}}">Назад к каталогу</a>
    <h1>Корзина</h1>
    @if($items->isEmpty())
        <p>Корзина пустаня</p>
    @else
        <table>
            <thead>
            <tr>
                <th>Пользователь</th>
                <th>Товар</th>
                <th>Категория</th>
                <th>Количество</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{$item->user?->email}}</td>
                    <td>{{$item->product?->name}}</td>
                    <td>{{$item->product?->category?->code}}</td>
                    <td>{{$item->quantity}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

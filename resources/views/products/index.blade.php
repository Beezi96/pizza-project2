<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Каталог</title>
</head>
<body>
    <h1>Каталог товаров</h1>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Описание</th>
            <th>Категория</th>
            <th>Открыть</th>
            <th>В корзину</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{$product->id}}</td>
                <td>{{$product->name}}</td>
                <td>{{$product->price}}</td>
                <td>{{$product->description}}</td>
                <td>{{$product->category->code}} ({{$product->category->name}})</td>
                <td>
                    <a href="{{url('/product/' . $product->id)}}">Открыть</a>
                </td>
                <td>
                    <form method="POST" action="{{route('cart.add')}}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{$product->id}}">
                        <button type="submit">Добавить</button>
                    </form>
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>

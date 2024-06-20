<!DOCTYPE html>
<html>
<head>
    <title>Fixtures</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
<h1>League Fixtures</h1>
@foreach($fixtures as $date => $courts)
    <h2>Date: {{ $date }}</h2>
    @foreach($courts as $court => $games)
        <h3>Court: {{ $court }}</h3>
        <table>
            <thead>
            <tr>
                <th>Time</th>
                <th>Game</th>
            </tr>
            </thead>
            <tbody>
            @foreach($games as $time => $game)
                <tr>
                    <td>{{ $time }}</td>
                    <td>Team {{ $game[0] }} vs Team {{ $game[1] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
@endforeach
</body>
</html>

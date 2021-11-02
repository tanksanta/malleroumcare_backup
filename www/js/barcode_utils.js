function isDuplicate(arr)  {
    const isDup = arr.some(function(x) {
        return arr.indexOf(x) !== arr.lastIndexOf(x);
    });

    return isDup;
}
def split(num):
    arr = []
    while num>0:
        arr = [num%10] + arr
        num = int(num/10)
    return arr

def pretty(arr):
    newstr = ''
    for i in range(0, len(arr)):
        newstr += str(arr[i])
    return int(newstr) if newstr!='' else 0

def nextLarger(num, arr):
    if len(arr)==0:
        minVal = num
        pos = 0
    else:
        pos = -1
        minVal = max(arr)+1
        for i in range(0,len(arr)):
            if (arr[i]-num)>0 and minVal>(arr[i]-num):
                minVal = (arr[i]-num)
                pos = i+1
    return pos

def permute(num):
    arr = split(num)
    dec = len(arr)-1
    while arr[dec]<arr[dec-1]:
        dec-=1
    if dec>0:
        lar = dec + nextLarger(arr[dec-1], arr[dec:]) - 1
        temp = arr[lar]
        arr[lar] = arr[dec-1]
        arr[dec-1] = temp
        right = len(arr)-1
        left = dec
        while left<right:
            if arr[right]<arr[left]:
                temp = arr[right]
                arr[right] = arr[left]
                arr[left] = temp
            left += 1
            right -= 1
    num = pretty(arr)
    return num

def permutation_generator(num):
    arr = split(num)
    maxVal = pretty(sorted(arr,reverse=True))
    while num!=maxVal:
        num = permute(num)
        yield num

aNum = 41523
print aNum
for i in permutation_generator(aNum):
    print i

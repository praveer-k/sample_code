def calendar_conflicts(cal):
    conflicts = []
    temp_conflicts = [ cal[0][2] ]
    end = cal[0][1]
    for i in range(1, len(cal)):
        if cal[i][0] >= end:
            # no conflict
            if len(temp_conflicts) > 1:
                conflicts.append(temp_conflicts)
            temp_conflicts = []
        end = max(cal[i][1], end)
        temp_conflicts.append(cal[i][2])
    if len(temp_conflicts) > 1:
        conflicts.append(temp_conflicts)
    return conflicts

meetings = [
    [1, 2, 'a'],
    [3, 5, 'b'],
    [4, 6, 'c'],
    [7, 10, 'd'],
    [8, 11, 'e'],
    [10, 12, 'f'],
    [13, 14, 'g'],
    [13, 14, 'h']
]
print len(meetings)
conflicts = calendar_conflicts(meetings)

print conflicts

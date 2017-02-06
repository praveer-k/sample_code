# import itertools
# friends = ['A','B','C','D']
# table_size = 3
# print [x for x in itertools.combinations(friends, table_size)]
def find_dinner_parties(friends, table_size):
    groups = combine_friends(friends, table_size)
    return groups

def combine_friends(friends, table_size, pos=0, group=[], groups=[]):
    if len(group)==table_size:
        groups.append(group)
    elif pos < len(friends):
        # leave
        combine_friends(friends, table_size, pos+1, group)
        # take
        new_group = list(group)
        new_group.append(friends[pos])
        combine_friends(friends, table_size, pos+1, new_group)
    return groups

friends = ['A','B','C','D']
table_size = 3

combinations = find_dinner_parties(friends, table_size)

print combinations

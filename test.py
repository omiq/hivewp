from beem.discussions import Query, Discussions_by_created
q = Query(limit=1,select_authors='makerhacks',author='makerhacks', parent_author='makerhacks')
for h in Discussions_by_created(q):
    print(h)

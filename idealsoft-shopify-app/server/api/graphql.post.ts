export default defineEventHandler(async (event) => {
    const body = await readBody(event)
    const graphqlUrl = process.env.NUXT_PUBLIC_GRAPHQL_URL || 'http://localhost:8000/graphql'

    const response = await $fetch(graphqlUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body,
    })

    return response
})

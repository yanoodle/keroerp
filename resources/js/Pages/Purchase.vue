<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import WarningButton from "@/Components/WarningButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import { nextTick, ref, computed, watch } from "vue";

const nameInput = ref(null);
const modal = ref(false);
const title = ref("");
const operation = ref(1);
const id = ref("");

const props = defineProps({
    purchases: { type: Object },
    inventories: { type: Object },
});
const form = useForm({
    product: "",
    quantity: "",
    price: "",
    status: "",
});
const formPage = useForm({});
const onPageClick = (event) => {
    formPage.get(route("purchases.index", { page: event }));
};
const openModal = (op, product, quantity, price, status, purchase) => {
    modal.value = true;
    nextTick(() => nameInput.value.focus());
    operation.value = op;
    id.value = purchase;
    if (op == 1) {
        title.value = "Add Entry";
    } else {
        title.value = "Edit Entry";
        form.product = product;
        form.quantity = quantity;
        form.price = price;
        form.status = status;
    }
};
const closeModal = () => {
    modal.value = false;
    form.reset();
};
const save = () => {
    if (operation.value == 1) {
        form.post(route("purchase.store"), {
            onSuccess: () => {
                ok("Entry Added");
            },
        });
    } else {
        form.put(route("purchase.update", id.value), {
            onSuccess: () => {
                ok("Entry updated");
            },
        });
    }
};
const ok = (msj) => {
    form.reset();
    closeModal();
    Swal.fire({ title: msj, icon: "success" });
};
const deletePurchase = (purchase) => {
    const alerta = Swal.mixin({
        buttonStyling: true,
    });
    alerta
        .fire({
            title: "This action will delete " + purchase.product,
            icon: "exclamation",
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm',
            cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
        })
        .then((result) => {
            if (result.isConfirmed) {
                form.delete(route("purchase.destroy", purchase), {
                    onSuccess: () => {
                        ok("Entry deleted");
                    },
                });
            }
        });
};

const formatToIDR = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const productMap = computed(() => {
    const map = {};
    props.inventories.forEach((product) => {
        map[product.name] = product.price;
    });
    return map;
});

watch(
    () => [form.product, form.quantity],
    ([product, quantity]) => {
        const basePrice = productMap.value[product] || 0;
        const qty = parseInt(quantity) || 0;
        form.price = basePrice * qty;
    }
);

const isEditMode = computed(() => title.value === "Edit Entry");

const updateStatus = (purchase, newStatus) => {
    Swal.fire({
        title: `Change status to ${newStatus}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
    }).then((result) => {
        if (result.isConfirmed) {
            const statusForm = useForm({
                product: purchase.product,
                quantity: purchase.quantity,
                price: purchase.price,
                status: newStatus,
            });

            statusForm.put(route("purchase.update", purchase.id), {
                onSuccess: () => {
                    purchase.status = newStatus;
                    Swal.fire({
                        title: `${newStatus} successfully!`,
                        icon: "success",
                    });
                },
                onError: () => {
                    Swal.fire({
                        title: "Error updating status",
                        icon: "error",
                    });
                },
            });
        }
    });
};

const dateSortOrder = ref("asc");

const toggleDateSort = () => {
    dateSortOrder.value = dateSortOrder.value === "asc" ? "desc" : "asc";
};

const searchQuery = ref("");
const statusFilter = ref("");

const filteredPurchase = computed(() => {
    let filtered = props.purchases.filter((purchase) => {
        const matchesSearch =
            purchase.product
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            purchase.status
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            new Date(purchase.created_at)
                .toLocaleDateString()
                .includes(searchQuery.value);

        const matchesStatus =
            statusFilter.value === "" || purchase.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    });

    filtered.sort((a, b) => {
        const dateA = new Date(a.created_at);
        const dateB = new Date(b.created_at);
        if (dateSortOrder.value === "asc") {
            return dateA - dateB;
        } else {
            return dateB - dateA;
        }
    });

    return filtered;
});

const statusColors = {
    Pending: "bg-yellow-100 text-yellow-800",
    Approved: "bg-blue-100 text-blue-800",
    Shipped: "bg-indigo-100 text-indigo-800",
    Received: "bg-purple-100 text-purple-800",
    Completed: "bg-green-100 text-green-800",
    Cancelled: "bg-red-100 text-red-800",
    Refunded: "bg-pink-100 text-pink-800",
};
</script>

<template>
    <Head title="Purchase" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Purchasing Module
            </h2>
        </template>

        <div
            class="py-12"
            v-if="
                $page.props.auth.user.role === 'Purchase' ||
                $page.props.auth.user.role === 'Admin'
            "
        >
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Welcome, You're logged in as Purchasing
                    </div>
                    <div
                        v-if="$page.props.auth.user.role === 'Admin'"
                        class="p-6 text-gray-900"
                    >
                        <PrimaryButton @click="($event) => openModal(1)">
                            Add
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <div
                class="m-6 flex flex-wrap gap-4 items-center bg-white p-4 rounded-lg shadow-md max-w-xl mx-auto"
            >
                <input
                    type="text"
                    placeholder="Search by product, status, or date..."
                    v-model="searchQuery"
                    class="flex-grow min-w-[200px] border border-gray-300 rounded-lg px-4 py-2 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                />

                <select
                    v-model="statusFilter"
                    class="w-40 border border-gray-300 rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <div class="mt-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto my-4">
                    <table
                        class="w-full text-sm text-left rtl:text-right text-gray-500"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 cursor-pointer select-none"
                                    @click="toggleDateSort"
                                >
                                    Date
                                    <span v-if="dateSortOrder === 'asc'"
                                        >▲</span
                                    >
                                    <span v-else>▼</span>
                                </th>
                                <th scope="col" class="px-6 py-3">Product</th>
                                <th scope="col" class="px-6 py-3">Quantity</th>
                                <th scope="col" class="px-6 py-3">Price</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3"></th>
                                <th scope="col" class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(inv, i) in filteredPurchase"
                                :key="inv.id"
                                class="bg-white border-b"
                            >
                                <td class="px-6 py-4">
                                    PO{{ String(i + 1).padStart(3, "0") }}
                                </td>
                                <td class="px-6 py-4">
                                    {{
                                        new Date(
                                            inv.created_at
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <th
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                                >
                                    {{ inv.product }}
                                </th>
                                <td class="px-6 py-4">{{ inv.quantity }}</td>
                                <td class="px-6 py-4">
                                    {{ formatToIDR(inv.price) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                            statusColors[inv.status],
                                        ]"
                                    >
                                        {{ inv.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <template v-if="inv.status === 'Pending'">
                                        <PrimaryButton
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Approved'
                                                    )
                                            "
                                        >
                                            ✔ Approve
                                        </PrimaryButton>
                                    </template>
                                    <template
                                        v-else-if="inv.status === 'Approved'"
                                    >
                                        <PrimaryButton
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Completed'
                                                    )
                                            "
                                        >
                                            ✅ Complete
                                        </PrimaryButton>
                                    </template>
                                    <template v-else>
                                        <!-- No button or disabled button when status is other than Pending or Approved -->
                                        <PrimaryButton disabled>
                                            {{ inv.status }}
                                        </PrimaryButton>
                                    </template>
                                </td>

                                <td class="px-6 py-4">
                                    <DangerButton
                                        @click="($event) => deletePurchase(inv)"
                                        type="button"
                                        class="text-white bg-red-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2"
                                    >
                                        Delete
                                    </DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="py-12" v-else>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're not authorized here...
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="modal" @close="closeModal">
            <h2 class="p-3 text-lg font.medium text-gray-900">{{ title }}</h2>
            <div class="p-3 mt-6">
                <InputLabel for="product" value="Product"></InputLabel>
                <select
                    id="product"
                    ref="nameInput"
                    v-model="form.product"
                    :disabled="isEditMode"
                    class="mt-1 block w-3/4 rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200"
                >
                    <option value="" disabled>Select a product</option>
                    <option
                        v-for="product in props.inventories"
                        :key="product.id"
                        :value="product.name"
                    >
                        {{ product.name }}
                    </option>
                </select>
                <InputError
                    :message="form.errors.product"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="quantity" value="Quantity"></InputLabel>
                <TextInput
                    id="quantity"
                    ref="nameInput"
                    v-model="form.quantity"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Quantity"
                    :readonly="isEditMode"
                ></TextInput>
                <InputError
                    :message="form.errors.quantity"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="price" value="Price"></InputLabel>
                <TextInput
                    id="price"
                    ref="nameInput"
                    v-model="form.price"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Price"
                    :readonly="true"
                />
                <InputError
                    :message="form.errors.price"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="status" value="Status"></InputLabel>
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-3/4 rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200"
                >
                    <option value="" disabled>Select a status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Received">Received</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                    <option value="Refunded">Refunded</option>
                </select>
                <InputError
                    :message="form.errors.status"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 mt-6">
                <PrimaryButton :disabled="form.processing" @click="save"
                    >Save</PrimaryButton
                >
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
